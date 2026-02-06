<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\IngredientInventory;
use App\Models\Branch;
use App\Models\Inventory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SimpliciteaInventorySeeder extends Seeder
{
    private $branches;

    /**
     * Seed the complete Simplicitea inventory including:
     * - Product Categories
     * - Products (Finished Goods) with size options
     * - Ingredients (Raw Materials) 
     * - Product-Ingredient recipes (BOM)
     * - Initial inventory levels per branch
     */
    public function run(): void
    {
        $this->command->info('Seeding Simplicitea Inventory...');

        // Get all branches for inventory distribution
        $this->branches = Branch::all();
        
        // Create categories
        $categories = $this->createCategories();
        
        // Create products (finished goods)
        $products = $this->createProducts($categories, $this->branches);
        
        // Create ingredients (raw materials) with branch-specific inventory
        $ingredients = $this->createIngredients();
        
        // Create recipe mappings (product-ingredient relationships)
        $this->createRecipes($products, $ingredients);
        
        $this->command->info('Simplicitea Inventory seeded successfully!');
    }

    private function createCategories(): array
    {
        $this->command->info('Creating categories...');
        
        $categoryData = [
            ['name' => 'Milk Tea: Chocolate', 'description' => 'Chocolate-based milk tea flavors', 'is_active' => true],
            ['name' => 'Milk Tea: Classic', 'description' => 'Classic milk tea flavors', 'is_active' => true],
            ['name' => 'Milk Tea: Best-Sellers', 'description' => 'Premium best-selling milk tea flavors', 'is_active' => true],
            ['name' => 'Frappe: Regular', 'description' => 'Blended frappe beverages', 'is_active' => true],
            ['name' => 'Fruit-Tea Yakult', 'description' => 'Fruit tea with Yakult', 'is_active' => true],
            ['name' => 'Burgers', 'description' => 'Burger meals', 'is_active' => true],
            ['name' => 'Chicken Wings', 'description' => 'Chicken wings in various sizes', 'is_active' => true],
            ['name' => 'Rice Meals', 'description' => 'Filipino rice meals', 'is_active' => true],
        ];

        $categories = [];
        foreach ($categoryData as $data) {
            $categories[$data['name']] = Category::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        return $categories;
    }

    private function createProducts(array $categories, $branches): array
    {
        $this->command->info('Creating products...');
        
        $products = [];

        // ========== MILK TEA: CHOCOLATE ==========
        $chocolateFlavors = ['Dark Choco', 'Rocky Road', 'Double Dutch', 'Oreo Chocolate', 'Belgian Chocolate'];
        foreach ($chocolateFlavors as $flavor) {
            $products[$flavor] = $this->createSizedMilkTea($flavor, $categories['Milk Tea: Chocolate'], 85, 95, 135, 'composite', $branches);
        }

        // ========== MILK TEA: CLASSIC ==========
        $classicFlavors = ['Okinawa', 'Hokkaido', 'Salted Caramel', 'Sakura Strawberry', 'Red Butter Caramel', 
                          'Classic Brown Sugar', 'Taro-Ube Creamcheese', 'Vanilla Cookie Crumble'];
        foreach ($classicFlavors as $flavor) {
            $products[$flavor] = $this->createSizedMilkTea($flavor, $categories['Milk Tea: Classic'], 85, 95, 135, 'composite', $branches);
        }

        // ========== MILK TEA: BEST-SELLERS ==========
        $bestSellerFlavors = ['DC Nutella', 'Wintermelon', 'Nutella Hershey\'s', 'Red Velvet Nutella', 
                              'Cheesecake Overload', 'Hershey\'s Dark Oreo', 'Red Velvet Overload', 
                              'Nutella Creamcheese', 'Matcha Oreo Cheesecake'];
        foreach ($bestSellerFlavors as $flavor) {
            $products[$flavor] = $this->createSizedMilkTea($flavor, $categories['Milk Tea: Best-Sellers'], 95, 105, 145, 'composite', $branches);
        }

        // ========== FRAPPE: REGULAR ==========
        $frappeFlavors = ['Ube Frappe', 'Melon Frappe', 'Mocha Frappe', 'Bubblegum Frappe', 
                          'Cappuccino Frappe', 'Choco Fudge Frappe', 'Double Dutch Frappe', 'Cookies \'n Cream Frappe'];
        foreach ($frappeFlavors as $flavor) {
            $products[$flavor] = Product::updateOrCreate(
                ['name' => $flavor],
                [
                    'category_id' => $categories['Frappe: Regular']->id,
                    'description' => 'Blended ' . $flavor,
                    'price' => 60, // Base price (16 oz)
                    'is_active' => true,
                    'product_type' => 'composite',
                    'options' => [
                        ['name' => 'Size', 'values' => [
                            ['label' => '16 oz', 'price' => 60, 'priceType' => 'fixed'],
                            ['label' => '22 oz', 'price' => 80, 'priceType' => 'fixed'],
                        ]]
                    ]
                ]
            );
            $this->createProductInventory($products[$flavor], $branches);
        }

        // ========== FRUIT-TEA YAKULT ==========
        $fruitTeaFlavors = ['Lemon Yakult', 'Mango Yakult', 'Blueberry Yakult', 'Strawberry Yakult', 
                           'Green Apple Yakult', 'Passion Fruit Yakult'];
        foreach ($fruitTeaFlavors as $flavor) {
            $products[$flavor] = $this->createSizedMilkTea($flavor, $categories['Fruit-Tea Yakult'], 75, 85, 135, 'composite', $branches);
        }

        // ========== BURGERS ==========
        $burgers = [
            ['name' => 'Regular Burger', 'solo' => 45, 'fries' => 79],
            ['name' => 'Cheeseburger', 'solo' => 55, 'fries' => 89],
            ['name' => 'Hawaiian Burger', 'solo' => 65, 'fries' => 95],
            ['name' => 'Burger Overload', 'solo' => 90, 'fries' => 105],
        ];
        foreach ($burgers as $burger) {
            $products[$burger['name']] = Product::updateOrCreate(
                ['name' => $burger['name']],
                [
                    'category_id' => $categories['Burgers']->id,
                    'description' => $burger['name'] . ' meal',
                    'price' => $burger['solo'],
                    'is_active' => true,
                    'product_type' => 'composite',
                    'options' => [
                        ['name' => 'Meal Type', 'values' => [
                            ['label' => 'Solo', 'price' => $burger['solo'], 'priceType' => 'fixed'],
                            ['label' => 'w/ Fries', 'price' => $burger['fries'], 'priceType' => 'fixed'],
                        ]]
                    ]
                ]
            );
            $this->createProductInventory($products[$burger['name']], $branches);
        }

        // ========== CHICKEN WINGS ==========
        $wings = [
            ['name' => 'Chicken Wings Solo', 'desc' => '3 pcs', 'price' => 100],
            ['name' => 'Chicken Wings Barkada', 'desc' => '9 pcs', 'price' => 280],
            ['name' => 'Chicken Wings Bilao', 'desc' => '18 pcs', 'price' => 680],
        ];
        foreach ($wings as $wing) {
            $products[$wing['name']] = Product::updateOrCreate(
                ['name' => $wing['name']],
                [
                    'category_id' => $categories['Chicken Wings']->id,
                    'description' => $wing['desc'] . ' chicken wings',
                    'price' => $wing['price'],
                    'is_active' => true,
                    'product_type' => 'composite',
                    'options' => null
                ]
            );
            $this->createProductInventory($products[$wing['name']], $branches);
        }

        // ========== RICE MEALS ==========
        $riceMeals = [
            ['name' => 'Hamsilog', 'price' => 60],
            ['name' => 'Hotsilog', 'price' => 60],
            ['name' => 'Tapsilog', 'price' => 85],
            ['name' => 'Spamsilog', 'price' => 80],
            ['name' => 'Sisig with Rice', 'price' => 100],
        ];
        foreach ($riceMeals as $meal) {
            $products[$meal['name']] = Product::updateOrCreate(
                ['name' => $meal['name']],
                [
                    'category_id' => $categories['Rice Meals']->id,
                    'description' => $meal['name'] . ' meal',
                    'price' => $meal['price'],
                    'is_active' => true,
                    'product_type' => 'composite',
                    'options' => null
                ]
            );
            $this->createProductInventory($products[$meal['name']], $branches);
        }

        return $products;
    }

    private function createSizedMilkTea(string $name, Category $category, float $price16, float $price22, float $priceLitro, string $type, $branches): Product
    {
        $product = Product::updateOrCreate(
            ['name' => $name],
            [
                'category_id' => $category->id,
                'description' => $name . ' Milk Tea',
                'price' => $price16, // Base price (16 oz)
                'is_active' => true,
                'product_type' => $type,
                'options' => [
                    ['name' => 'Size', 'values' => [
                        ['label' => '16 oz', 'price' => $price16, 'priceType' => 'fixed'],
                        ['label' => '22 oz', 'price' => $price22, 'priceType' => 'fixed'],
                        ['label' => 'Litro', 'price' => $priceLitro, 'priceType' => 'fixed'],
                    ]]
                ]
            ]
        );
        
        $this->createProductInventory($product, $branches);
        
        return $product;
    }

    private function createProductInventory(Product $product, $branches): void
    {
        foreach ($branches as $branch) {
            Inventory::updateOrCreate(
                ['product_id' => $product->id, 'branch_id' => $branch->id],
                [
                    'quantity' => rand(50, 200), // Random initial stock
                    'min_stock_level' => 10
                ]
            );
        }
    }

    private function createIngredients(): array
    {
        $this->command->info('Creating ingredients...');
        
        $ingredients = [];

        // ========== TEAS/BASES ==========
        $teas = [
            ['name' => 'Assam Black Tea Leaves', 'unit' => 'g', 'qty' => 5000, 'min' => 500],
            ['name' => 'Jasmine Green Tea Leaves', 'unit' => 'g', 'qty' => 5000, 'min' => 500],
            ['name' => 'Brewed Tea Base', 'unit' => 'ml', 'qty' => 20000, 'min' => 2000],
        ];
        foreach ($teas as $item) {
            $ingredients[$item['name']] = $this->createIngredientWithInventory(
                $item['name'],
                'Tea/Base ingredient',
                $item['unit'],
                $item['qty'],
                $item['min']
            );
        }

        // ========== POWDERS ==========
        $powders = [
            ['name' => 'Dark Choco Powder', 'qty' => 3000],
            ['name' => 'Matcha Powder', 'qty' => 2000],
            ['name' => 'Taro Powder', 'qty' => 2500],
            ['name' => 'Okinawa Powder', 'qty' => 2500],
            ['name' => 'Wintermelon Powder', 'qty' => 3000],
            ['name' => 'Hokkaido Powder', 'qty' => 2500],
            ['name' => 'Rocky Road Powder', 'qty' => 2000],
            ['name' => 'Double Dutch Powder', 'qty' => 2000],
            ['name' => 'Belgian Chocolate Powder', 'qty' => 2000],
            ['name' => 'Red Velvet Powder', 'qty' => 2500],
            ['name' => 'Ube Powder', 'qty' => 2000],
            ['name' => 'Melon Powder', 'qty' => 2000],
            ['name' => 'Mocha Powder', 'qty' => 2500],
            ['name' => 'Bubblegum Powder', 'qty' => 1500],
            ['name' => 'Cappuccino Powder', 'qty' => 2500],
            ['name' => 'Cookies n Cream Powder', 'qty' => 2000],
            ['name' => 'Strawberry Powder', 'qty' => 2000],
            ['name' => 'Salted Caramel Powder', 'qty' => 2000],
            ['name' => 'Brown Sugar Powder', 'qty' => 3000],
            ['name' => 'Vanilla Powder', 'qty' => 2000],
        ];
        foreach ($powders as $item) {
            $ingredients[$item['name']] = $this->createIngredientWithInventory(
                $item['name'],
                'Powder flavoring',
                'g',
                $item['qty'],
                300
            );
        }

        // ========== DAIRY ==========
        $dairy = [
            ['name' => 'Non-Dairy Creamer', 'unit' => 'g', 'qty' => 10000, 'min' => 1000],
            ['name' => 'Fresh Milk', 'unit' => 'ml', 'qty' => 15000, 'min' => 2000],
            ['name' => 'Condensed Milk', 'unit' => 'ml', 'qty' => 8000, 'min' => 1000],
            ['name' => 'Evaporated Milk', 'unit' => 'ml', 'qty' => 8000, 'min' => 1000],
            ['name' => 'Cream Cheese', 'unit' => 'g', 'qty' => 3000, 'min' => 500],
        ];
        foreach ($dairy as $item) {
            $ingredients[$item['name']] = $this->createIngredientWithInventory(
                $item['name'],
                'Dairy ingredient',
                $item['unit'],
                $item['qty'],
                $item['min']
            );
        }

        // ========== SINKERS ==========
        $sinkers = [
            ['name' => 'Black Pearls (Tapioca)', 'qty' => 5000],
            ['name' => 'Nata de Coco', 'qty' => 3000],
            ['name' => 'Coffee Jelly', 'qty' => 3000],
            ['name' => 'Rainbow Jelly', 'qty' => 2500],
        ];
        foreach ($sinkers as $item) {
            $ingredients[$item['name']] = $this->createIngredientWithInventory(
                $item['name'],
                'Sinker/Add-on',
                'g',
                $item['qty'],
                500
            );
        }

        // ========== SYRUPS ==========
        $syrups = [
            ['name' => 'Fructose Syrup', 'qty' => 10000],
            ['name' => 'Brown Sugar Syrup', 'qty' => 8000],
            ['name' => 'Nutella', 'qty' => 5000],
            ['name' => 'Hershey\'s Chocolate Syrup', 'qty' => 5000],
            ['name' => 'Caramel Syrup', 'qty' => 4000],
            ['name' => 'Strawberry Syrup', 'qty' => 3000],
            ['name' => 'Vanilla Syrup', 'qty' => 3000],
        ];
        foreach ($syrups as $item) {
            $ingredients[$item['name']] = $this->createIngredientWithInventory(
                $item['name'],
                'Syrup/Sweetener',
                'ml',
                $item['qty'],
                500
            );
        }

        // ========== FOOD: MEAT ==========
        $meats = [
            ['name' => 'Burger Patties', 'qty' => 100],
            ['name' => 'Hotdogs', 'qty' => 100],
            ['name' => 'Ham Slices', 'qty' => 80],
            ['name' => 'Chicken Wings (raw)', 'qty' => 200],
            ['name' => 'Beef Tapa', 'qty' => 80],
            ['name' => 'Spam', 'qty' => 60],
            ['name' => 'Sisig', 'qty' => 50],
        ];
        foreach ($meats as $item) {
            $ingredients[$item['name']] = $this->createIngredientWithInventory(
                $item['name'],
                'Food: Meat component',
                'pcs',
                $item['qty'],
                20
            );
        }

        // ========== FOOD: SIDES ==========
        $sides = [
            ['name' => 'Frozen French Fries', 'unit' => 'g', 'qty' => 10000, 'min' => 1000],
            ['name' => 'Nacho Chips', 'unit' => 'g', 'qty' => 3000, 'min' => 500],
            ['name' => 'Rice', 'unit' => 'g', 'qty' => 20000, 'min' => 2000],
            ['name' => 'Eggs', 'unit' => 'pcs', 'qty' => 200, 'min' => 30],
        ];
        foreach ($sides as $item) {
            $ingredients[$item['name']] = $this->createIngredientWithInventory(
                $item['name'],
                'Food: Side component',
                $item['unit'],
                $item['qty'],
                $item['min']
            );
        }

        // ========== TOPPINGS ==========
        $toppings = [
            ['name' => 'Oreo Bits', 'qty' => 2000],
            ['name' => 'Graham Cracker Crumbs', 'qty' => 2000],
            ['name' => 'Rock Salt & Cheese Cream', 'qty' => 3000],
            ['name' => 'Whipped Cream', 'qty' => 2000],
            ['name' => 'Cheese Foam', 'qty' => 3000],
        ];
        foreach ($toppings as $item) {
            $ingredients[$item['name']] = $this->createIngredientWithInventory(
                $item['name'],
                'Topping/Finishing',
                'g',
                $item['qty'],
                300
            );
        }

        // ========== FRUIT INGREDIENTS ==========
        $fruits = [
            ['name' => 'Lemon Juice', 'unit' => 'ml', 'qty' => 5000],
            ['name' => 'Mango Puree', 'unit' => 'ml', 'qty' => 5000],
            ['name' => 'Blueberry Puree', 'unit' => 'ml', 'qty' => 4000],
            ['name' => 'Strawberry Puree', 'unit' => 'ml', 'qty' => 5000],
            ['name' => 'Green Apple Syrup', 'unit' => 'ml', 'qty' => 4000],
            ['name' => 'Passion Fruit Puree', 'unit' => 'ml', 'qty' => 4000],
            ['name' => 'Yakult', 'unit' => 'pcs', 'qty' => 200],
        ];
        foreach ($fruits as $item) {
            $ingredients[$item['name']] = $this->createIngredientWithInventory(
                $item['name'],
                'Fruit ingredient',
                $item['unit'],
                $item['qty'],
                $item['unit'] === 'pcs' ? 30 : 500
            );
        }

        return $ingredients;
    }

    /**
     * Create an ingredient and its branch-specific inventory records.
     */
    private function createIngredientWithInventory(
        string $name,
        string $description,
        string $unit,
        float $defaultQty,
        float $minStock
    ): Ingredient {
        // Create or update the ingredient (no quantity stored here anymore)
        $ingredient = Ingredient::updateOrCreate(
            ['name' => $name],
            [
                'description' => $description,
                'unit' => $unit,
                'is_active' => true
            ]
        );

        // Create inventory record for each branch
        foreach ($this->branches as $branch) {
            IngredientInventory::updateOrCreate(
                ['ingredient_id' => $ingredient->id, 'branch_id' => $branch->id],
                [
                    'quantity' => $defaultQty,
                    'min_stock_level' => $minStock
                ]
            );
        }

        return $ingredient;
    }

    private function createRecipes(array $products, array $ingredients): void
    {
        $this->command->info('Creating product recipes (BOM)...');

        // Base amounts for different sizes (16 oz, 22 oz, Litro)
        // These are approximate amounts per serving
        $teaBase16oz = 200; // ml
        $powder16oz = 30; // g
        $creamer16oz = 25; // g
        $fructose16oz = 30; // ml
        $pearls16oz = 50; // g

        // ========== CHOCOLATE MILK TEA RECIPES ==========
        $this->attachRecipe($products, $ingredients, 'Dark Choco', [
            ['Brewed Tea Base', $teaBase16oz, 'ml'],
            ['Dark Choco Powder', $powder16oz, 'g'],
            ['Non-Dairy Creamer', $creamer16oz, 'g'],
            ['Fructose Syrup', $fructose16oz, 'ml'],
            ['Black Pearls (Tapioca)', $pearls16oz, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Rocky Road', [
            ['Brewed Tea Base', $teaBase16oz, 'ml'],
            ['Rocky Road Powder', $powder16oz, 'g'],
            ['Non-Dairy Creamer', $creamer16oz, 'g'],
            ['Fructose Syrup', $fructose16oz, 'ml'],
            ['Black Pearls (Tapioca)', $pearls16oz, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Double Dutch', [
            ['Brewed Tea Base', $teaBase16oz, 'ml'],
            ['Double Dutch Powder', $powder16oz, 'g'],
            ['Non-Dairy Creamer', $creamer16oz, 'g'],
            ['Fructose Syrup', $fructose16oz, 'ml'],
            ['Black Pearls (Tapioca)', $pearls16oz, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Oreo Chocolate', [
            ['Brewed Tea Base', $teaBase16oz, 'ml'],
            ['Dark Choco Powder', $powder16oz, 'g'],
            ['Non-Dairy Creamer', $creamer16oz, 'g'],
            ['Fructose Syrup', $fructose16oz, 'ml'],
            ['Black Pearls (Tapioca)', $pearls16oz, 'g'],
            ['Oreo Bits', 15, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Belgian Chocolate', [
            ['Brewed Tea Base', $teaBase16oz, 'ml'],
            ['Belgian Chocolate Powder', $powder16oz, 'g'],
            ['Non-Dairy Creamer', $creamer16oz, 'g'],
            ['Fructose Syrup', $fructose16oz, 'ml'],
            ['Black Pearls (Tapioca)', $pearls16oz, 'g'],
        ]);

        // ========== CLASSIC MILK TEA RECIPES ==========
        $this->attachRecipe($products, $ingredients, 'Okinawa', [
            ['Brewed Tea Base', $teaBase16oz, 'ml'],
            ['Okinawa Powder', $powder16oz, 'g'],
            ['Non-Dairy Creamer', $creamer16oz, 'g'],
            ['Brown Sugar Syrup', 20, 'ml'],
            ['Black Pearls (Tapioca)', $pearls16oz, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Hokkaido', [
            ['Brewed Tea Base', $teaBase16oz, 'ml'],
            ['Hokkaido Powder', $powder16oz, 'g'],
            ['Non-Dairy Creamer', $creamer16oz, 'g'],
            ['Fructose Syrup', $fructose16oz, 'ml'],
            ['Black Pearls (Tapioca)', $pearls16oz, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Salted Caramel', [
            ['Brewed Tea Base', $teaBase16oz, 'ml'],
            ['Salted Caramel Powder', $powder16oz, 'g'],
            ['Non-Dairy Creamer', $creamer16oz, 'g'],
            ['Caramel Syrup', 20, 'ml'],
            ['Black Pearls (Tapioca)', $pearls16oz, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Classic Brown Sugar', [
            ['Brewed Tea Base', $teaBase16oz, 'ml'],
            ['Brown Sugar Powder', $powder16oz, 'g'],
            ['Non-Dairy Creamer', $creamer16oz, 'g'],
            ['Brown Sugar Syrup', 30, 'ml'],
            ['Black Pearls (Tapioca)', $pearls16oz, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Taro-Ube Creamcheese', [
            ['Brewed Tea Base', $teaBase16oz, 'ml'],
            ['Taro Powder', 20, 'g'],
            ['Ube Powder', 15, 'g'],
            ['Non-Dairy Creamer', $creamer16oz, 'g'],
            ['Cream Cheese', 20, 'g'],
            ['Fructose Syrup', $fructose16oz, 'ml'],
            ['Black Pearls (Tapioca)', $pearls16oz, 'g'],
        ]);

        // ========== BEST-SELLERS RECIPES ==========
        $this->attachRecipe($products, $ingredients, 'Wintermelon', [
            ['Brewed Tea Base', $teaBase16oz, 'ml'],
            ['Wintermelon Powder', $powder16oz, 'g'],
            ['Non-Dairy Creamer', $creamer16oz, 'g'],
            ['Fructose Syrup', $fructose16oz, 'ml'],
            ['Black Pearls (Tapioca)', $pearls16oz, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'DC Nutella', [
            ['Brewed Tea Base', $teaBase16oz, 'ml'],
            ['Dark Choco Powder', $powder16oz, 'g'],
            ['Nutella', 25, 'ml'],
            ['Non-Dairy Creamer', $creamer16oz, 'g'],
            ['Fructose Syrup', $fructose16oz, 'ml'],
            ['Black Pearls (Tapioca)', $pearls16oz, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Nutella Hershey\'s', [
            ['Brewed Tea Base', $teaBase16oz, 'ml'],
            ['Nutella', 25, 'ml'],
            ['Hershey\'s Chocolate Syrup', 20, 'ml'],
            ['Non-Dairy Creamer', $creamer16oz, 'g'],
            ['Fructose Syrup', 20, 'ml'],
            ['Black Pearls (Tapioca)', $pearls16oz, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Matcha Oreo Cheesecake', [
            ['Brewed Tea Base', $teaBase16oz, 'ml'],
            ['Matcha Powder', $powder16oz, 'g'],
            ['Cream Cheese', 20, 'g'],
            ['Non-Dairy Creamer', $creamer16oz, 'g'],
            ['Fructose Syrup', $fructose16oz, 'ml'],
            ['Oreo Bits', 20, 'g'],
            ['Black Pearls (Tapioca)', $pearls16oz, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Hershey\'s Dark Oreo', [
            ['Brewed Tea Base', $teaBase16oz, 'ml'],
            ['Dark Choco Powder', $powder16oz, 'g'],
            ['Hershey\'s Chocolate Syrup', 25, 'ml'],
            ['Non-Dairy Creamer', $creamer16oz, 'g'],
            ['Oreo Bits', 20, 'g'],
            ['Black Pearls (Tapioca)', $pearls16oz, 'g'],
        ]);

        // ========== FRUIT-TEA YAKULT RECIPES ==========
        $fruitTeas = [
            'Lemon Yakult' => 'Lemon Juice',
            'Mango Yakult' => 'Mango Puree',
            'Blueberry Yakult' => 'Blueberry Puree',
            'Strawberry Yakult' => 'Strawberry Puree',
            'Green Apple Yakult' => 'Green Apple Syrup',
            'Passion Fruit Yakult' => 'Passion Fruit Puree',
        ];
        foreach ($fruitTeas as $product => $fruit) {
            $this->attachRecipe($products, $ingredients, $product, [
                ['Jasmine Green Tea Leaves', 5, 'g'],
                [$fruit, 60, 'ml'],
                ['Yakult', 2, 'pcs'],
                ['Fructose Syrup', 25, 'ml'],
                ['Nata de Coco', 30, 'g'],
            ]);
        }

        // ========== FRAPPE RECIPES ==========
        $this->attachRecipe($products, $ingredients, 'Ube Frappe', [
            ['Ube Powder', 40, 'g'],
            ['Fresh Milk', 100, 'ml'],
            ['Non-Dairy Creamer', 20, 'g'],
            ['Fructose Syrup', 30, 'ml'],
            ['Whipped Cream', 20, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Mocha Frappe', [
            ['Mocha Powder', 40, 'g'],
            ['Fresh Milk', 100, 'ml'],
            ['Non-Dairy Creamer', 20, 'g'],
            ['Fructose Syrup', 30, 'ml'],
            ['Whipped Cream', 20, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Cookies \'n Cream Frappe', [
            ['Cookies n Cream Powder', 40, 'g'],
            ['Fresh Milk', 100, 'ml'],
            ['Non-Dairy Creamer', 20, 'g'],
            ['Fructose Syrup', 30, 'ml'],
            ['Oreo Bits', 15, 'g'],
            ['Whipped Cream', 20, 'g'],
        ]);

        // ========== BURGER RECIPES ==========
        $this->attachRecipe($products, $ingredients, 'Regular Burger', [
            ['Burger Patties', 1, 'pcs'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Cheeseburger', [
            ['Burger Patties', 1, 'pcs'],
            ['Cream Cheese', 20, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Hawaiian Burger', [
            ['Burger Patties', 1, 'pcs'],
            ['Ham Slices', 1, 'pcs'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Burger Overload', [
            ['Burger Patties', 2, 'pcs'],
            ['Cream Cheese', 30, 'g'],
            ['Frozen French Fries', 50, 'g'],
        ]);

        // ========== CHICKEN WINGS RECIPES ==========
        $this->attachRecipe($products, $ingredients, 'Chicken Wings Solo', [
            ['Chicken Wings (raw)', 3, 'pcs'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Chicken Wings Barkada', [
            ['Chicken Wings (raw)', 9, 'pcs'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Chicken Wings Bilao', [
            ['Chicken Wings (raw)', 18, 'pcs'],
        ]);

        // ========== RICE MEALS RECIPES ==========
        $this->attachRecipe($products, $ingredients, 'Hamsilog', [
            ['Ham Slices', 2, 'pcs'],
            ['Eggs', 1, 'pcs'],
            ['Rice', 150, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Hotsilog', [
            ['Hotdogs', 2, 'pcs'],
            ['Eggs', 1, 'pcs'],
            ['Rice', 150, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Tapsilog', [
            ['Beef Tapa', 1, 'pcs'],
            ['Eggs', 1, 'pcs'],
            ['Rice', 150, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Spamsilog', [
            ['Spam', 1, 'pcs'],
            ['Eggs', 1, 'pcs'],
            ['Rice', 150, 'g'],
        ]);

        $this->attachRecipe($products, $ingredients, 'Sisig with Rice', [
            ['Sisig', 1, 'pcs'],
            ['Rice', 150, 'g'],
        ]);
    }

    private function attachRecipe(array $products, array $ingredients, string $productName, array $recipe): void
    {
        if (!isset($products[$productName])) {
            $this->command->warn("Product not found: {$productName}");
            return;
        }

        $product = $products[$productName];
        $syncData = [];

        foreach ($recipe as $item) {
            [$ingredientName, $quantity, $unit] = $item;
            
            if (!isset($ingredients[$ingredientName])) {
                $this->command->warn("Ingredient not found: {$ingredientName}");
                continue;
            }

            $ingredient = $ingredients[$ingredientName];
            $syncData[$ingredient->id] = [
                'quantity_required' => $quantity,
                'unit' => $unit
            ];
        }

        if (!empty($syncData)) {
            $product->ingredients()->sync($syncData);
        }
    }
}
