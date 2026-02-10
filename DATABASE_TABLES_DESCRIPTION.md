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
We stored the addresses for each user. This includes city, district, and street. We select the user_id field because users can have multiple addresses, and these addresses are for profile purposes only, not required for ride registration. This table has a many-to-one relationship with users table.

## User Settings Table
We stored the user preferences and settings. This includes language preference (Arabic or English) and notification enabled flag. We select the user_id field because each user has one settings record, and we need to apply these settings to personalize the user experience. The language setting can be used to localize the dashboard interface. This table has a one-to-one relationship with users table.

## OTP Verifications Table
We stored the OTP (One-Time Password) codes sent for phone and email verification. This includes user_id (nullable for unregistered users), identifier (email or phone), type (email or phone), OTP code (6 digits), is_verified flag, expires_at timestamp, and verified_at timestamp. We select the identifier and type fields because we need to verify OTP codes for both email and phone verification, and we index these fields for faster lookups during verification.

