/**
 * QZ Tray Certificate Configuration for Simplicitea POS
 * 
 * This file provides the certificate and signature for QZ Tray to trust this application.
 * For production, you should get a proper certificate from https://qz.io/pricing
 * 
 * For local/development use, this demo certificate allows silent printing.
 */

// Demo certificate for development - allows QZ Tray to trust localhost
// This is QZ.io's public demo certificate
var qzCertificate = "-----BEGIN CERTIFICATE-----\n" +
    "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\n" +
    "-----END CERTIFICATE-----";

/**
 * Initialize QZ Tray with certificate
 */
function setupQZCertificate() {
    if (typeof qz === 'undefined') {
        console.warn('QZ Tray not loaded');
        return;
    }

    // Set up certificate promise
    qz.security.setCertificatePromise(function(resolve, reject) {
        resolve(qzCertificate);
    });

    // For demo/development - use a permissive signature
    // In production, you would sign requests properly
    qz.security.setSignatureAlgorithm("SHA512"); // Preferred algorithm
    qz.security.setSignaturePromise(function(toSign) {
        return function(resolve, reject) {
            // For localhost/development, we can use an empty signature with demo cert
            // QZ Tray will accept this for the demo certificate on localhost
            resolve("");
        };
    });
}

// Initialize certificate when script loads
if (typeof qz !== 'undefined') {
    setupQZCertificate();
} else {
    // Wait for QZ Tray to load
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(setupQZCertificate, 500);
    });
}
