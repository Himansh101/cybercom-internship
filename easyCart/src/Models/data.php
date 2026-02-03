<?php
// 1. Categories
$categories = [
    'cat_01' => 'Electronics',
    'cat_02' => 'Fashion',
    'cat_03' => 'Home & Living',
    'cat_04' => 'Sports'
];

// 2. Brands
$brands = [
    'br_01' => ['name' => 'Aurora', 'tag' => 'Smart Tech & Active Wear'],
    'br_02' => ['name' => 'ZenStyle', 'tag' => 'Minimalist Staples'],
    'br_03' => ['name' => 'HomeX', 'tag' => 'Modern Living Essentials'],
    'br_04' => ['name' => 'PeakFit', 'tag' => 'Professional Grade Gear']
];

// 3. Products (Updated with 'in_stock' key)
$products = [
    101 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Noise Cancelling Headphones',
        'stock_count' => 15,
        'price' => 1999,
        'image' => 'images/noiseCancelHeadphones.png',
        'images' => [
            'images/noiseCancelHeadphones.png',
            'images/headphones.png',
            'images/wirelessEarbuds.png'
        ],
        'description' => 'Premium audio experience with active noise cancellation.',
        'is_featured' => true,
        'in_stock' => true,
        'item_shipping_type' => 'freight'
    ],
    102 => [
        'cat_id' => 'cat_04',
        'brand_id' => 'br_01',
        'name' => 'Smart Fitness Watch',
        'stock_count' => 0,
        'price' => 299,
        'image' => 'images/smartWatch.png',
        'images' => [
            'images/smartWatch.png',
            'images/smartHub.png'
        ],
        'description' => 'Advanced health tracking for the modern athlete.',
        'is_featured' => true,
        'in_stock' => false,
        'item_shipping_type' => 'express'
    ],
    103 => [
        'cat_id' => 'cat_02',
        'brand_id' => 'br_02',
        'name' => 'Classic Leather Tote',
        'stock_count' => 12,
        'price' => 199,
        'image' => 'images/leatherBag.png',
        'images' => [
            'images/leatherBag.png',
            'images/leatherTote.png'
        ],
        'description' => 'Spacious and elegant leather tote for daily use.',
        'is_featured' => false,
        'in_stock' => true,
        'item_shipping_type' => 'express'
    ],
    104 => [
        'cat_id' => 'cat_03',
        'brand_id' => 'br_03',
        'name' => 'Compact Air Purifier',
        'stock_count' => 10, 
        'price' => 799,
        'image' => 'images/airPurifier.png',
        'images' => [
            'images/airPurifier.png',
            'images/aromaDiffuser.png'
        ],
        'description' => 'HEPA filtration for a cleaner home environment.',
        'is_featured' => false,
        'in_stock' => true,
        'item_shipping_type' => 'freight'
    ],
    105 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_03',
        'name' => 'Home Smart Hub',
        'stock_count' => 0,
        'price' => 999,
        'image' => 'images/smartHub.png',
        'images' => [
            'images/smartHub.png',
            'images/bluetoothSpeaker.png'
        ],
        'description' => 'Control your entire home from one centralized device.',
        'is_featured' => false,
        'in_stock' => false,
        'item_shipping_type' => 'freight'
    ],
    106 => [
        'cat_id' => 'cat_04',
        'brand_id' => 'br_04',
        'name' => 'Lightweight Running Shoes',
        'stock_count' => 8,
        'price' => 249,
        'image' => 'images/runningShoes.png',
        'images' => [
            'images/runningShoes.png'
        ],
        'description' => 'Maximum comfort for long distance endurance.',
        'is_featured' => true,
        'in_stock' => true,
        'item_shipping_type' => 'express'
    ],
    107 => [
        'cat_id' => 'cat_04',
        'brand_id' => 'br_04',
        'name' => 'Yoga Performance Mat',
        'stock_count' => 18,
        'price' => 899,
        'image' => 'images/yogaMat.png',
        'images' => [
            'images/yogaMat.png'
        ],
        'description' => 'Eco-friendly, non-slip surface for advanced poses.',
        'is_featured' => false,
        'in_stock' => true,
        'item_shipping_type' => 'freight'
    ],
    108 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 0,
        'price' => 500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false,
        'item_shipping_type' => 'freight'
    ],
    109 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 0,
        'price' => 200,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false,
        'item_shipping_type' => 'express'
    ],
    110 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 0,
        'price' => 100,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false,
        'item_shipping_type' => 'express'
    ],
    111 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 20,
        'price' => 400,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => true,
        'item_shipping_type' => 'freight'
    ],
    112 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 0,
        'price' => 500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false,
        'item_shipping_type' => 'freight'
    ],
    113 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 11,
        'price' => 200,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => true,
        'item_shipping_type' => 'express'
    ],
    114 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 0,
        'price' => 600,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false,
        'item_shipping_type' => 'freight'
    ],
    115 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 14,
        'price' => 200,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => true,
        'item_shipping_type' => 'express'
    ],
    116 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 0,
        'price' => 250,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false,
        'item_shipping_type' => 'express'
    ],
    117 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 0,
        'price' => 270,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false,
        'item_shipping_type' => 'express'
    ],
    118 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 17,
        'price' => 400,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => true,
        'item_shipping_type' => 'freight'
    ],
    119 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 0,
        'price' => 490,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false,
        'item_shipping_type' => 'freight'
    ],
    120 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 21,
        'price' => 150,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => true,
        'item_shipping_type' => 'express'
    ],
    121 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 14,
        'price' => 140,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => true,
        'in_stock' => true,
        'item_shipping_type' => 'express'
    ],
    123 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 14,
        'price' => 500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => true,
        'in_stock' => true,
        'item_shipping_type' => 'freight'
    ],
    124 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 14,
        'price' => 300,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => true,
        'in_stock' => true,
        'item_shipping_type' => 'express'
    ],
    125 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 14,
        'price' => 290,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => true,
        'in_stock' => true,
        'item_shipping_type' => 'express'
    ],
    126 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 14,
        'price' => 500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => true,
        'in_stock' => true,
        'item_shipping_type' => 'freight'
    ],
    127 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 14,
        'price' => 250,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => true,
        'in_stock' => true,
        'item_shipping_type' => 'express'
    ],
    128 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 14,
        'price' => 500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => true,
        'in_stock' => true,
        'item_shipping_type' => 'freight'
    ],
    129 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 14,
        'price' => 200,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => true,
        'in_stock' => true,
        'item_shipping_type' => 'express'
    ],
    130 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 14,
        'price' => 460,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => true,
        'in_stock' => true,
        'item_shipping_type' => 'freight'
    ],
    131 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 14,
        'price' => 180,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => true,
        'in_stock' => true,
        'item_shipping_type' => 'express'
    ],
    122 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'stock_count' => 0,
        'price' => 400,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false,
        'item_shipping_type' => 'freight'
    ]
];

// 4. Orders
$orders = [
    [
        'order_id' => 'ORD-99201',
        'date' => 'Jan 18, 2026',
        'total' => 18598,
        'status' => 'delivered',
        'current_step' => 4,
        'items' => [101, 102]
    ],
    [
        'order_id' => 'ORD-99202',
        'date' => 'Jan 21, 2026',
        'total' => 8999,
        'status' => 'processing',
        'current_step' => 2,
        'items' => [105]
    ]
];
