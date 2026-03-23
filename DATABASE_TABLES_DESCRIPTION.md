# Database Tables Description

## Users Table
We stored the personal information and authentication data for each user in the system. This includes basic details like name, email, phone, password, and profile image. We also store the user's role (admin, coach, or rider), gender, age, birthday, profession, language preference, and account status (is_active, is_coach_approved). The email_verified_at and phone_verified_at fields track verification status for email and phone number respectively. We select the role field because users can have different permissions based on their role in the cycling team.

## User Fitness Profiles Table
We stored the fitness and health information for each user. This includes physical attributes like height (in centimeters) and weight (in kilograms), medical notes, other sports activities, last ride date, and the maximum distance the user can cycle (max_distance_km). We select the max_distance_km field because it is used to determine the initial user level when they first register for a ride. This table has a one-to-one relationship with users table, meaning each user can have only one fitness profile.

## User Levels Table
We stored the dynamic performance metrics and current level for each user. This includes the level name (Beginner, Intermediate, or Advanced), level number (1, 2, 3...), total distance covered, total rides completed, total points earned, and last update timestamp. We select the level_name and level_number fields because they determine which rides a user can join. The system automatically updates these metrics after each completed ride and promotes users to higher levels based on performance (3 beginner rides → Intermediate 1, 6 intermediate rides → Advanced 1). This table has a one-to-one relationship with users table.

## Rides Table
We stored the information about cycling rides organized by the team. This includes ride title, location, difficulty level (Beginner, Intermediate, or Advanced), distance, start time, gathering time, end time, image URL, break location, cost, and GPS coordinates (start and end points). We select the created_by field because only admins can create rides, and we need to track which admin created each ride. The level field is important because it determines which users can join the ride based on their current level.

## Ride Participants Table
We stored the participation records for users who join rides. This includes the ride_id and user_id foreign keys, role (rider or coach), status (joined, cancelled, excused, completed, or no_show), timestamps for different status changes (joined_at, cancelled_at, excused_at, completed_at, checked_at), and performance metrics (distance_km, avg_speed_kmh, calories_burned, points_earned). We select the role field because it differentiates between regular riders and coaches participating in the same ride, allowing admins to filter participants by role. We select the status field because it tracks the participant's journey from joining to completion, and we need to know if they cancelled, excused, completed, or didn't show up.

## Ride Tracks Table
We stored the GPS tracking points recorded during rides for each participant. This includes the ride_id and ride_participant_id foreign keys, GPS coordinates (latitude and longitude), speed at each point, and the timestamp when the point was recorded (recorded_at). We select the ride_participant_id field because we need to track the complete route for each participant separately, and we can calculate the total distance and average speed from these GPS points. This data is used to automatically mark rides as completed and update user performance metrics.

## Coaches Table
We stored the professional information for coaches in the team. This includes bio, years of experience, image URL, specialty, certificate, and rating. We select the user_id field because coaches are actual users in the system (with role = 'coach' in users table), and we link this table to the users table to get their personal information. Only admins can manage coach profiles, and coaches participate in rides with role = 'coach' in the ride_participants table.

## Events Table
We stored the information about team events organized periodically. This includes event title, description, image URL, location, cost, start time, and end time. We select the created_by field because only admins can create events, and we need to track which admin created each event. Events are displayed publicly on the home screen for all users to view.

## Memories Table
We stored the team memories in the form of old photos. This includes image path, description, is_active flag, and photo date. We select the is_active field because only active memories are displayed in the app, allowing admins to control which memories are visible. These memories are general team memories, not linked to specific rides or users. Only admins can manage memories.

## Addresses Table
We stored the addresses for each user. This includes city, district, and street. We select the user_id field because users can have multiple addresses. These addresses are used for order delivery - when a user places an order, they select one of their saved addresses, and the admin uses this address along with the user's phone number (from users table) to coordinate delivery with the delivery service outside the system. This table has a many-to-one relationship with users table and a one-to-many relationship with orders table.

## User Settings Table
We stored the user preferences and settings. This includes language preference (Arabic or English) and notification enabled flag. We select the user_id field because each user has one settings record, and we need to apply these settings to personalize the user experience. The language setting can be used to localize the dashboard interface. This table has a one-to-one relationship with users table.

## OTP Verifications Table
We stored the OTP (One-Time Password) codes sent for phone and email verification. This includes user_id (nullable for unregistered users), identifier (email or phone), type (email or phone), OTP code (6 digits), is_verified flag, expires_at timestamp, and verified_at timestamp. We select the identifier and type fields because we need to verify OTP codes for both email and phone verification, and we index these fields for faster lookups during verification.

---

## E-Commerce Tables (Store System)

## Brands Table
We stored the brand information for products in the store. This includes brand name, logo, description, and is_active flag. We select the is_active field because only active brands are displayed in the store, allowing admins to control which brands are visible. This table has a one-to-many relationship with products table, meaning each brand can have multiple products.

## Categories Table
We stored the category information for organizing products. This includes category name, slug (for URL-friendly names), description, image, parent_id (for subcategories), and is_active flag. We select the parent_id field because categories can have subcategories, creating a hierarchical structure. We select the slug field because it is used in product URLs for SEO purposes. This table has a self-referential relationship (parent-child) and a one-to-many relationship with products table.

## Products Table
We stored the product information for the store. This includes product name, slug, description, short_description, price, compare_price (original price before discount), SKU (Stock Keeping Unit), stock_quantity, is_in_stock flag, brand_id, category_id, is_active flag, is_featured flag, weight, and created_by. We select the brand_id and category_id fields because products belong to a brand and a category, allowing users to filter products by brand or category. We select the SKU field because it is a unique identifier for inventory management. We select the is_featured field because featured products are displayed prominently on the home page. This table has many-to-one relationships with brands and categories tables, and a one-to-many relationship with product_images, carts, favorites, order_items, and product_reviews tables.

## Product Images Table
We stored multiple images for each product. This includes product_id, image_path, is_primary flag (to mark the main product image), and order (to sort images). We select the is_primary field because the primary image is displayed as the main product image in listings. We select the order field because images can be sorted and displayed in a specific sequence. This table has a many-to-one relationship with products table, meaning each product can have multiple images.

## Related Products Table
We stored the relationships between products to show related products on product detail pages. This includes product_id and related_product_id. We select both fields because we need to link products together, and we use a unique constraint to prevent duplicate relationships. This table creates a many-to-many relationship between products, allowing each product to have multiple related products.

## Coupons Table
We stored discount coupons for the store. This includes code (unique coupon code), name, description, type (percentage or fixed amount), value (discount value), minimum_amount (minimum order amount to use coupon), maximum_discount (maximum discount for percentage coupons), usage_limit (total usage limit), usage_count (current usage count), user_limit (usage limit per user), starts_at, expires_at, is_active flag, and created_by. We select the code field because users enter this code to apply the discount. We select the type field because coupons can be percentage-based or fixed amount discounts. We select the usage_limit and usage_count fields because coupons can have a limited number of uses. This table has a one-to-many relationship with orders table.

## Carts Table
We stored the shopping cart items for each user. This includes user_id, product_id, and quantity. We select the user_id and product_id fields because each cart item belongs to a user and a product. We use a unique constraint on user_id and product_id to prevent duplicate items in the cart (quantity is updated instead). This table has many-to-one relationships with users and products tables.

## Favorites Table
We stored the favorite products for each user. This includes user_id and product_id. We select both fields because users can add products to their favorites list. We use a unique constraint on user_id and product_id to prevent duplicate favorites. This table has many-to-one relationships with users and products tables.

## Orders Table
We stored the order information for purchases. This includes order_number (unique order identifier), user_id, address_id (links to addresses table for delivery address), status (pending, confirmed, processing, shipped, delivered, cancelled, returned), subtotal, discount, total, payment_method (cash or online only), payment_status, delivered_at (timestamp when order was delivered), shipping_address (JSON containing full address details including phone number), notes, coupon_id, cancelled_at, cancelled_by, and cancellation_reason. We select the order_number field because it is a unique identifier shown to users for order tracking. We select the address_id field because it links the order to a specific address from the user's saved addresses, allowing the admin to contact the delivery service using the phone number from the user's profile. We select the status field because it tracks the order lifecycle from creation to delivery. We select the delivered_at field because it records when the order was delivered, and we use it to enforce the 3-day return policy (users can only return orders within 3 days of delivery). We select the shipping_address field because it stores the complete delivery address as JSON (including city, district, street, and phone number from user profile). We select the coupon_id field because orders can use discount coupons. This table has many-to-one relationships with users, addresses, and coupons tables, and one-to-many relationships with order_items, payments, and order_returns tables.

## Order Items Table
We stored the individual items in each order. This includes order_id, product_id, product_name (saved at time of order), product_price (saved at time of order), quantity, subtotal (quantity × product_price), status (active, cancelled, returned), cancelled_at, and returned_at. We select the product_name and product_price fields because product details may change over time, so we save a snapshot at the time of order. We select the subtotal field because it calculates the total for this item. We select the status field because it tracks the state of each item (active, cancelled, or returned), allowing users to cancel or return individual items from an order. We select the cancelled_at and returned_at fields because they record when an item was cancelled or returned, and we use cancelled_at to enforce the 1-hour cancellation policy. This table has many-to-one relationships with orders and products tables, and a one-to-many relationship with order_returns table.

## Payments Table
We stored the payment records for orders. This includes payment_number (unique payment identifier), order_id, user_id, method (cash or online), amount, status (pending, processing, completed, failed, refunded), transaction_id (from payment gateway), payment_details (JSON for additional payment information), paid_at timestamp, and notes. We select the payment_number field because it is a unique identifier for payment tracking. We select the transaction_id field because it links to the payment gateway transaction. We select the payment_details field because different payment methods may require different information. This table has many-to-one relationships with orders and users tables.

## Order Returns Table
We stored the return requests for orders. This includes return_number (unique return identifier), order_id, order_item_id (nullable, for returning specific items or entire order), user_id, reason, status (pending, approved, rejected, refunded), refund_amount, refund_method, admin_notes, approved_by, and approved_at. We select the return_number field because it is a unique identifier for return tracking. We select the order_item_id field because users can return specific items or the entire order. We select the status field because it tracks the return process from request to refund. This table has many-to-one relationships with orders, order_items, and users tables.

## Product Reviews Table
We stored the product reviews and ratings from users. This includes product_id, user_id, order_id (nullable, to verify purchase), rating (1-5), review (text), is_approved flag, and is_visible flag. We select the order_id field because we can verify that the user actually purchased the product before allowing a review. We select the rating field because it provides a numerical rating (1-5 stars). We select the is_approved and is_visible fields because admins can moderate reviews before they are displayed. We use a unique constraint on product_id and user_id to prevent duplicate reviews. This table has many-to-one relationships with products, users, and orders tables.

## Notifications Table
We stored the notifications for users about order updates and other events. This includes user_id, type (order_created, order_status_changed, payment_received, etc.), title, message, data (JSON for additional information), is_read flag, and read_at timestamp. We select the type field because different notification types may have different handling logic. We select the data field because notifications can include additional information like order_id. We select the is_read field because we need to track which notifications users have seen. This table has a many-to-one relationship with users table.

