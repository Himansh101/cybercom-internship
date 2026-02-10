-- Drop existing tables to allow for clean recreation
drop table if exists sales_order_address cascade;
drop table if exists sales_order_item cascade;
drop table if exists sales_order cascade;
drop table if exists sales_cart_metadata cascade;
drop table if exists sales_cart_address cascade;
drop table if exists sales_cart_product cascade;
drop table if exists sales_cart cascade;
drop table if exists catalog_product_image cascade;
drop table if exists catalog_product_attribute cascade;
drop table if exists catalog_category_product cascade;
drop table if exists catalog_product_entity cascade;
drop table if exists catalog_category_attribute cascade;
drop table if exists catalog_category_entity cascade;
drop table if exists catalog_brand_entity cascade;
drop table if exists customer_entity cascade;
drop table if exists sales_shipping_method cascade;
drop table if exists sales_coupon cascade;

-- table: catalog_brand_entity
create table catalog_brand_entity (
    entity_id varchar(50) primary key, -- Using the br_01 type IDs as PK for simplicity in migration
    name varchar(100) not null,
    description text
);

-- table: catalog_product_entity
create table catalog_product_entity (
    entity_id serial primary key,
    sku varchar(100) not null unique,
    name varchar(255) not null,
    price decimal(12, 2) not null,
    stock_count int default 0,
    url_key varchar(255) unique,
    created_at timestamp default current_timestamp
);

-- table: catalog_product_attribute
create table catalog_product_attribute (
    attribute_id serial primary key,
    entity_id int references catalog_product_entity(entity_id) on delete cascade,
    attribute_key varchar(100) not null, -- e.g., 'color', 'size', 'material'
    attribute_value text not null
);

-- table: catalog_category_entity
create table catalog_category_entity (
    entity_id serial primary key,
    name varchar(100) not null unique,
    description text
);

-- table: catalog_category_attribute
create table catalog_category_attribute (
    attribute_id serial primary key,
    entity_id int references catalog_category_entity(entity_id) on delete cascade,
    attribute_key varchar(100) not null,
    attribute_value text not null
);

-- table: catalog_category_product
create table catalog_category_product (
    increment_id serial primary key,
    category_id int references catalog_category_entity(entity_id) on delete cascade,
    product_id int references catalog_product_entity(entity_id) on delete cascade
);

-- table: catalog_product_image
create table catalog_product_image (
    image_id serial primary key,
    product_id int references catalog_product_entity(entity_id) on delete cascade,
    image_url varchar(255) not null,
    is_main_image boolean default false
);

-- table: sales_cart
create table sales_cart (
    cart_id serial primary key,
    session_id varchar(255) not null, -- store the session_id from php here
    user_id int null, -- link if user logs in
    is_active boolean default true,
    created_at timestamp default current_timestamp
);

-- table: sales_cart_product
create table sales_cart_product (
    increment_id serial primary key,
    cart_id int references sales_cart(cart_id) on delete cascade,
    product_id int references catalog_product_entity(entity_id),
    quantity int not null default 1
);

-- table: sales_cart_metadata
create table sales_cart_metadata (
    metadata_id serial primary key,
    cart_id int unique references sales_cart(cart_id) on delete cascade,
    shipping_method varchar(50) default 'standard',
    coupon_code varchar(50) null
);

-- table: sales_cart_address
create table sales_cart_address (
    address_id serial primary key,
    cart_id int references sales_cart(cart_id) on delete cascade,
    email varchar(255),
    full_name varchar(255),
    street_address text,
    city varchar(100),
    pincode varchar(20)
);

-- table: sales_order
create table sales_order (
    order_id serial primary key,
    user_id int null,
    order_number varchar(100) unique not null,
    subtotal decimal(12, 2) not null,
    shipping_cost decimal(12, 2) not null,
    tax_amount decimal(12, 2) not null,
    final_amount decimal(12, 2) not null,
    status varchar(50) default 'pending',
    created_at timestamp default current_timestamp
);

-- table: sales_order_item
create table sales_order_item (
    item_id serial primary key,
    order_id int references sales_order(order_id) on delete cascade,
    product_id int references catalog_product_entity(entity_id),
    product_name_snapshot varchar(255) not null, -- record name at time of purchase
    price_snapshot decimal(12, 2) not null, -- record price at time of purchase
    quantity int not null
);

-- table: sales_order_address
create table sales_order_address (
    address_id serial primary key,
    order_id int references sales_order(order_id) on delete cascade,
    full_name varchar(255),
    street_address text,
    city varchar(100),
    pincode varchar(20)
);

-- table: customer_entity
create table customer_entity (
    entity_id serial primary key,
    name varchar(255) not null,
    email varchar(255) unique not null,
    mobile varchar(20),
    password varchar(255) not null,
    street_address text,
    city varchar(100),
    pincode varchar(20),
    is_admin boolean default false,
    created_at timestamp default current_timestamp
);

-- table: sales_shipping_method
create table sales_shipping_method (
    code varchar(50) primary key,
    name varchar(100) not null,
    type varchar(20) not null, -- 'flat', 'percentage'
    base_cost decimal(10, 2) not null,
    is_active boolean default true
);

-- table: sales_coupon
create table sales_coupon (
    code varchar(50) primary key,
    discount_percent decimal(5, 2) not null,
    is_active boolean default true
);
