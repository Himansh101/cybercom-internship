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
        'price' => 10999,
        'image' => 'images/noiseCancelHeadphones.png',
        'images' => [
            'images/noiseCancelHeadphones.png',
            'images/headphones.png',
            'images/wirelessEarbuds.png'
        ],
        'description' => 'Premium audio experience with active noise cancellation.',
        'is_featured' => true,
        'in_stock' => true
    ],
    102 => [
        'cat_id' => 'cat_04',
        'brand_id' => 'br_01',
        'name' => 'Smart Fitness Watch',
        'price' => 7599,
        'image' => 'images/smartWatch.png',
        'images' => [
            'images/smartWatch.png',
            'images/smartHub.png'
        ],
        'description' => 'Advanced health tracking for the modern athlete.',
        'is_featured' => true,
        'in_stock' => false
    ],
    103 => [
        'cat_id' => 'cat_02',
        'brand_id' => 'br_02',
        'name' => 'Classic Leather Tote',
        'price' => 10199,
        'image' => 'images/leatherBag.png',
        'images' => [
            'images/leatherBag.png',
            'images/leatherTote.png'
        ],
        'description' => 'Spacious and elegant leather tote for daily use.',
        'is_featured' => false,
        'in_stock' => true
    ],
    104 => [
        'cat_id' => 'cat_03',
        'brand_id' => 'br_03',
        'name' => 'Compact Air Purifier',
        'price' => 12799,
        'image' => 'images/airPurifier.png',
        'images' => [
            'images/airPurifier.png',
            'images/aromaDiffuser.png'
        ],
        'description' => 'HEPA filtration for a cleaner home environment.',
        'is_featured' => false,
        'in_stock' => true
    ],
    105 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_03',
        'name' => 'Home Smart Hub',
        'price' => 8999,
        'image' => 'images/smartHub.png',
        'images' => [
            'images/smartHub.png',
            'images/bluetoothSpeaker.png'
        ],
        'description' => 'Control your entire home from one centralized device.',
        'is_featured' => false,
        'in_stock' => false
    ],
    106 => [
        'cat_id' => 'cat_04',
        'brand_id' => 'br_04',
        'name' => 'Lightweight Running Shoes',
        'price' => 8499,
        'image' => 'images/runningShoes.png',
        'images' => [
            'images/runningShoes.png'
        ],
        'description' => 'Maximum comfort for long distance endurance.',
        'is_featured' => true,
        'in_stock' => true
    ],
    107 => [
        'cat_id' => 'cat_04',
        'brand_id' => 'br_04',
        'name' => 'Yoga Performance Mat',
        'price' => 3899,
        'image' => 'images/yogaMat.png',
        'images' => [
            'images/yogaMat.png'
        ],
        'description' => 'Eco-friendly, non-slip surface for advanced poses.',
        'is_featured' => false,
        'in_stock' => true
    ],
    108 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'price' => 4500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false
    ],
    109 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'price' => 4500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false
    ],
    110 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'price' => 4500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false
    ],
    111 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'price' => 4500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false
    ],
    112 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'price' => 4500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false
    ],
    113 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'price' => 4500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false
    ],
    114 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'price' => 4500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false
    ],
    115 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'price' => 4500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false
    ],
    116 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'price' => 4500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false
    ],
    117 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'price' => 4500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false
    ],
    118 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'price' => 4500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false
    ],
    119 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'price' => 4500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false
    ],
    120 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'price' => 4500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false
    ],
    121 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'price' => 4500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => true,
        'in_stock' => false
    ],
    122 => [
        'cat_id' => 'cat_01',
        'brand_id' => 'br_01',
        'name' => 'Mechanical Keyboard',
        'price' => 4500,
        'image' => 'images/mechanicalKeyboard.png',
        'images' => [
            'images/mechanicalKeyboard.png'
        ],
        'description' => 'Tactile feedback for faster typing and gaming.',
        'is_featured' => false,
        'in_stock' => false
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
