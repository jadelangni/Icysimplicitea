/**
 * QZ Tray Silent Printing Module for Simplicitea POS
 * 
 * This module handles silent receipt printing using QZ Tray.
 * Download QZ Tray from: https://qz.io/download/
 */

let qzReady = false;
let qzPrinter = null;

/**
 * Initialize QZ Tray connection
 */
async function initQZTray() {
    if (typeof qz === 'undefined') {
        console.warn('QZ Tray library not loaded. Silent printing unavailable.');
        return false;
    }

    try {
        if (!qz.websocket.isActive()) {
            await qz.websocket.connect();
        }
        qzReady = true;
        console.log('QZ Tray connected successfully');
        
        // Find default printer
        qzPrinter = await qz.printers.getDefault();
        console.log('Default printer:', qzPrinter);
        
        return true;
    } catch (error) {
        console.warn('QZ Tray not available:', error.message);
        qzReady = false;
        return false;
    }
}

/**
 * Silent print receipt using QZ Tray (HTML mode)
 */
async function silentPrintReceipt(printUrl) {
    if (!qzReady) {
        const connected = await initQZTray();
        if (!connected) {
            // Fallback to browser print
            console.log('Falling back to browser print');
            return browserPrintFallback(printUrl);
        }
    }

    try {
        // Fetch the receipt HTML
        const response = await fetch(printUrl);
        const html = await response.text();

        // Configure print settings for thermal receipt printer
        const config = qz.configs.create(qzPrinter, {
            size: { width: 80, height: null }, // 80mm width, auto height
            units: 'mm',
            margins: { top: 0, right: 0, bottom: 0, left: 0 },
            colorType: 'blackwhite',
            copies: 1
        });

        // Print the HTML content
        const data = [{
            type: 'html',
            format: 'plain',
            data: html
        }];

        await qz.print(config, data);
        console.log('Receipt printed silently');
        return true;
    } catch (error) {
        console.error('Silent print failed:', error);
        return browserPrintFallback(printUrl);
    }
}

/**
 * Silent print using RAW ESC/POS commands (for thermal printers)
 */
async function silentPrintRaw(receiptData) {
    if (!qzReady) {
        const connected = await initQZTray();
        if (!connected) {
            console.error('QZ Tray not available for RAW printing');
            return false;
        }
    }

    try {
        const config = qz.configs.create(qzPrinter, { encoding: 'UTF-8' });
        
        // Build ESC/POS commands
        const data = buildEscPosReceipt(receiptData);
        
        await qz.print(config, data);
        console.log('RAW receipt printed');
        return true;
    } catch (error) {
        console.error('RAW print failed:', error);
        return false;
    }
}

/**
 * Build ESC/POS receipt commands
 */
function buildEscPosReceipt(receipt) {
    const ESC = '\x1B';
    const GS = '\x1D';
    const commands = [];

    // Initialize printer
    commands.push(ESC + '@');
    
    // Center alignment
    commands.push(ESC + 'a' + '\x01');
    
    // Bold on, double height
    commands.push(ESC + 'E' + '\x01');
    commands.push(GS + '!' + '\x11');
    commands.push(receipt.store_name + '\n');
    
    // Normal text
    commands.push(GS + '!' + '\x00');
    commands.push(ESC + 'E' + '\x00');
    commands.push(receipt.branch_name + '\n');
    commands.push('Receipt: ' + receipt.receipt_number + '\n');
    commands.push(receipt.date + '\n');
    
    // Divider
    commands.push('--------------------------------\n');
    
    // Left alignment for items
    commands.push(ESC + 'a' + '\x00');
    
    // Items
    receipt.items.forEach(item => {
        commands.push(item.name + '\n');
        if (item.options) {
            commands.push('  ' + item.options + '\n');
        }
        const line = `  ${item.quantity} x ${formatCurrency(item.unit_price)}`;
        const total = formatCurrency(item.total_price);
        const padding = 32 - line.length - total.length;
        commands.push(line + ' '.repeat(Math.max(1, padding)) + total + '\n');
    });
    
    // Divider
    commands.push('--------------------------------\n');
    
    // Totals - right aligned values
    commands.push(formatLine('Subtotal', formatCurrency(receipt.subtotal)));
    if (receipt.tax_amount > 0) {
        commands.push(formatLine('Tax', formatCurrency(receipt.tax_amount)));
    }
    if (receipt.discount_amount > 0) {
        commands.push(formatLine('Discount', '-' + formatCurrency(receipt.discount_amount)));
    }
    
    // Grand total (bold)
    commands.push(ESC + 'E' + '\x01');
    commands.push(formatLine('TOTAL', formatCurrency(receipt.total_amount)));
    commands.push(ESC + 'E' + '\x00');
    
    // Divider
    commands.push('--------------------------------\n');
    
    // Payment info
    commands.push(formatLine('Payment', receipt.payment_method));
    commands.push(formatLine('Amount Paid', formatCurrency(receipt.amount_paid)));
    commands.push(formatLine('Change', formatCurrency(receipt.change_amount)));
    
    // Footer
    commands.push('--------------------------------\n');
    commands.push(ESC + 'a' + '\x01'); // Center
    commands.push('Thank you for your purchase!\n');
    commands.push('Please come again\n');
    commands.push('\n');
    commands.push('Served by: ' + receipt.cashier + '\n');
    
    // Feed and cut
    commands.push('\n\n\n');
    commands.push(GS + 'V' + '\x00'); // Full cut

    return [{ type: 'raw', format: 'plain', data: commands.join('') }];
}

function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2);
}

function formatLine(label, value) {
    const padding = 32 - label.length - value.length;
    return label + ' '.repeat(Math.max(1, padding)) + value + '\n';
}

/**
 * Fallback to browser print when QZ Tray is not available
 */
function browserPrintFallback(printUrl) {
    let printFrame = document.getElementById('receipt-print-frame');
    
    if (!printFrame) {
        printFrame = document.createElement('iframe');
        printFrame.id = 'receipt-print-frame';
        printFrame.name = 'receipt-print-frame';
        printFrame.style.cssText = 'position:absolute;width:0;height:0;border:0;left:-9999px;top:-9999px;';
        document.body.appendChild(printFrame);
    }
    
    printFrame.src = printUrl;
    return false;
}

/**
 * Check if QZ Tray is available
 */
function isQZTrayAvailable() {
    return qzReady;
}

/**
 * Get list of available printers
 */
async function getAvailablePrinters() {
    if (!qzReady) {
        await initQZTray();
    }
    
    if (qzReady) {
        return await qz.printers.find();
    }
    return [];
}

/**
 * Set the printer to use
 */
function setQZPrinter(printerName) {
    qzPrinter = printerName;
    localStorage.setItem('pos_printer', printerName);
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Try to initialize QZ Tray
    setTimeout(initQZTray, 1000);
});
