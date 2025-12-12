-- ============================================
-- Pakistan Sample Data - 7 Entries Per Table
-- ============================================

-- ============================================
-- 1. ROLES TABLE
-- ============================================
INSERT INTO `roles` (`role_name`, `description`, `created_at`, `updated_at`) VALUES
('director', 'Director - Head Office (Islamabad) - Can view all GEs and their complaints', NOW(), NOW()),
('garrison_engineer', 'Garrison Engineer (GE) - per city - Can view/manage complaint centers under his city', NOW(), NOW()),
('complaint_center', 'Complaint Center (Helpdesk staff) - Can register and track complaints for their area only', NOW(), NOW()),
('department_staff', 'Trade/Department Staff - Receive and register complaint, assign to concerned department', NOW(), NOW()),
('admin', 'System Administrator - Full access to all modules', NOW(), NOW()),
('manager', 'Manager - Management level access', NOW(), NOW()),
('employee', 'Employee - Standard employee access', NOW(), NOW());

-- ============================================
-- 2. CITIES TABLE
-- ============================================
INSERT INTO `cities` (`name`, `province`, `description`, `status`, `created_at`, `updated_at`) VALUES
('Islamabad', 'Islamabad Capital Territory', 'Federal Capital of Pakistan', 'active', NOW(), NOW()),
('Lahore', 'Punjab', 'Second largest city of Pakistan, cultural hub', 'active', NOW(), NOW()),
('Karachi', 'Sindh', 'Largest city and financial capital of Pakistan', 'active', NOW(), NOW()),
('Rawalpindi', 'Punjab', 'Twin city of Islamabad, military headquarters', 'active', NOW(), NOW()),
('Faisalabad', 'Punjab', 'Third largest city, textile hub', 'active', NOW(), NOW()),
('Multan', 'Punjab', 'City of Saints, known for mangoes', 'active', NOW(), NOW()),
('Peshawar', 'Khyber Pakhtunkhwa', 'Gateway to Khyber Pass, historical city', 'active', NOW(), NOW());

-- ============================================
-- 3. SECTORS TABLE (assuming city_id 1-7)
-- ============================================
INSERT INTO `sectors` (`city_id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Sector G-6', 'Government sector in Islamabad', 'active', NOW(), NOW()),
(1, 'Sector F-7', 'Commercial sector in Islamabad', 'active', NOW(), NOW()),
(2, 'Model Town', 'Residential area in Lahore', 'active', NOW(), NOW()),
(2, 'Gulberg', 'Business district in Lahore', 'active', NOW(), NOW()),
(3, 'Clifton', 'Upscale area in Karachi', 'active', NOW(), NOW()),
(3, 'Defence Housing Authority', 'Residential area in Karachi', 'active', NOW(), NOW()),
(4, 'Cantonment Area', 'Military residential area in Rawalpindi', 'active', NOW(), NOW());

-- ============================================
-- 4. DESIGNATIONS TABLE
-- ============================================
INSERT INTO `designations` (`category`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
('Electric', 'Electrical Engineer', 'Senior electrical engineer', 'active', NOW(), NOW()),
('Electric', 'Electrician', 'Skilled electrician', 'active', NOW(), NOW()),
('Plumbing', 'Plumbing Supervisor', 'Supervises plumbing work', 'active', NOW(), NOW()),
('Plumbing', 'Plumber', 'Skilled plumber', 'active', NOW(), NOW()),
('HVAC', 'HVAC Technician', 'HVAC system technician', 'active', NOW(), NOW()),
('Building & Maintenance', 'Maintenance Officer', 'Building maintenance officer', 'active', NOW(), NOW()),
('IT Support', 'IT Support Specialist', 'IT support and troubleshooting', 'active', NOW(), NOW());

-- ============================================
-- 5. USERS TABLE (assuming role_id 1-7, city_id 1-7, sector_id 1-7)
-- ============================================
INSERT INTO `users` (`username`, `password`, `email`, `phone`, `role_id`, `city_id`, `sector_id`, `status`, `theme`, `created_at`, `updated_at`) VALUES
('director.isb', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'director@cms.pk', '03001234567', 1, 1, NULL, 'active', 'auto', NOW(), NOW()),
('ge.lahore', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ge.lahore@cms.pk', '03001234568', 2, 2, NULL, 'active', 'auto', NOW(), NOW()),
('cc.karachi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cc.karachi@cms.pk', '03001234569', 3, 3, 5, 'active', 'auto', NOW(), NOW()),
('dept.electrical', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'electrical@cms.pk', '03001234570', 4, 1, 1, 'active', 'auto', NOW(), NOW()),
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@cms.pk', '03001234571', 5, 1, NULL, 'active', 'auto', NOW(), NOW()),
('manager.rwp', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager.rwp@cms.pk', '03001234572', 6, 4, NULL, 'active', 'auto', NOW(), NOW()),
('employee.001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'emp001@cms.pk', '03001234573', 7, 2, 3, 'active', 'auto', NOW(), NOW());

-- ============================================
-- 6. COMPLAINT_CATEGORIES TABLE
-- ============================================
INSERT INTO `complaint_categories` (`name`, `description`, `created_at`, `updated_at`) VALUES
('Electric', 'Electrical complaints and issues', NOW(), NOW()),
('Plumbing', 'Water supply, drainage, and plumbing issues', NOW(), NOW()),
('HVAC', 'Heating, cooling, and ventilation issues', NOW(), NOW()),
('Building & Maintenance', 'Structural maintenance and repair complaints', NOW(), NOW()),
('IT Support', 'Computer, network, and IT related issues', NOW(), NOW()),
('Security', 'Security related complaints and concerns', NOW(), NOW()),
('Other', 'Other miscellaneous complaints', NOW(), NOW());

-- ============================================
-- 7. COMPLAINT_TITLES TABLE
-- ============================================
INSERT INTO `complaint_titles` (`category`, `title`, `description`, `created_at`, `updated_at`) VALUES
('Electric', 'Power Outage', 'No electricity supply in the area', NOW(), NOW()),
('Electric', 'Short Circuit', 'Electrical short circuit issue', NOW(), NOW()),
('Plumbing', 'Water Leakage', 'Water leaking from pipes or taps', NOW(), NOW()),
('Plumbing', 'Blocked Drain', 'Drainage blockage issue', NOW(), NOW()),
('HVAC', 'AC Not Working', 'Air conditioning system not functioning', NOW(), NOW()),
('Building & Maintenance', 'Cracked Wall', 'Wall cracks and structural damage', NOW(), NOW()),
('IT Support', 'Network Issue', 'Internet or network connectivity problem', NOW(), NOW());

-- ============================================
-- 8. CLIENTS TABLE
-- ============================================
INSERT INTO `clients` (`client_name`, `contact_person`, `email`, `phone`, `address`, `city`, `sector`, `state`, `status`, `created_at`, `updated_at`) VALUES
('Muhammad Ali', 'Muhammad Ali', 'm.ali@email.com', '03001234574', 'House 123, Street 5, Sector G-6', 'Islamabad', 'Sector G-6', 'Islamabad Capital Territory', 'active', NOW(), NOW()),
('Fatima Sheikh', 'Fatima Sheikh', 'f.sheikh@email.com', '03001234575', 'Model Town Block A, Lahore', 'Lahore', 'Model Town', 'Punjab', 'active', NOW(), NOW()),
('Ahmed Khan', 'Ahmed Khan', 'a.khan@email.com', '03001234576', 'Flat 201, Clifton Block 9', 'Karachi', 'Clifton', 'Sindh', 'active', NOW(), NOW()),
('Zainab Malik', 'Zainab Malik', 'z.malik@email.com', '03001234577', 'House 45, Road 3, Cantonment', 'Rawalpindi', 'Cantonment Area', 'Punjab', 'active', NOW(), NOW()),
('Hassan Raza', 'Hassan Raza', 'h.raza@email.com', '03001234578', 'Plot 78, Jinnah Road, Faisalabad', 'Faisalabad', 'Jinnah Road', 'Punjab', 'active', NOW(), NOW()),
('Ayesha Bibi', 'Ayesha Bibi', 'a.bibi@email.com', '03001234579', 'Street 12, Multan Cantt', 'Multan', 'Cantonment', 'Punjab', 'active', NOW(), NOW()),
('Bilal Ahmed', 'Bilal Ahmed', 'b.ahmed@email.com', '03001234580', 'Housing Scheme Phase 1, Peshawar', 'Peshawar', 'Phase 1', 'Khyber Pakhtunkhwa', 'active', NOW(), NOW());

-- ============================================
-- 9. EMPLOYEES TABLE (assuming city_id 1-7, sector_id 1-7)
-- ============================================
INSERT INTO `employees` (`name`, `email`, `department`, `designation`, `phone`, `biometric_id`, `date_of_hire`, `leave_quota`, `address`, `city_id`, `sector_id`, `status`, `created_at`, `updated_at`) VALUES
('Ahmed Hassan', 'ahmed.hassan@cms.pk', 'Electrical', 'Electrical Engineer', '03001234581', 'EMP001', '2020-01-15', 30, 'Sector F-7, Islamabad', 1, 2, 'active', NOW(), NOW()),
('Sara Malik', 'sara.malik@cms.pk', 'Plumbing', 'Plumbing Supervisor', '03001234582', 'EMP002', '2019-06-20', 30, 'Gulberg, Lahore', 2, 4, 'active', NOW(), NOW()),
('Ali Raza', 'ali.raza@cms.pk', 'HVAC', 'HVAC Technician', '03001234583', 'EMP003', '2021-03-10', 30, 'DHA Phase 5, Karachi', 3, 6, 'active', NOW(), NOW()),
('Fatima Khan', 'fatima.khan@cms.pk', 'Building & Maintenance', 'Maintenance Officer', '03001234584', 'EMP004', '2018-11-05', 30, 'Cantonment Area, Rawalpindi', 4, 7, 'active', NOW(), NOW()),
('Usman Ali', 'usman.ali@cms.pk', 'IT Support', 'IT Support Specialist', '03001234585', 'EMP005', '2022-02-01', 30, 'Sector G-6, Islamabad', 1, 1, 'active', NOW(), NOW()),
('Hina Sheikh', 'hina.sheikh@cms.pk', 'Electrical', 'Electrician', '03001234586', 'EMP006', '2020-08-12', 30, 'Model Town Block B, Lahore', 2, 3, 'active', NOW(), NOW()),
('Zain Butt', 'zain.butt@cms.pk', 'Plumbing', 'Plumber', '03001234587', 'EMP007', '2021-07-25', 30, 'Clifton Block 2, Karachi', 3, 5, 'active', NOW(), NOW());

-- ============================================
-- 10. SPARES TABLE
-- ============================================
INSERT INTO `spares` (`product_code`, `brand_name`, `item_name`, `category`, `unit_price`, `total_received_quantity`, `issued_quantity`, `stock_quantity`, `threshold_level`, `supplier`, `description`, `last_stock_in_at`, `created_at`, `updated_at`) VALUES
('ELEC001', 'Osaka', 'Electrical Wire 12 AWG', 'Electric', 2500.00, 100, 30, 70, 20, 'Karachi Electrical Suppliers', 'Copper wire 12 AWG, 100 meters roll', NOW(), NOW(), NOW()),
('PLUMB001', 'Master', 'PVC Pipe 1 inch', 'Plumbing', 800.00, 200, 80, 120, 30, 'Lahore Pipe Industries', 'PVC pipe 1 inch diameter, 10 feet length', NOW(), NOW(), NOW()),
('HVAC001', 'Dawlance', 'AC Filter', 'HVAC', 1500.00, 50, 15, 35, 10, 'Cooling Solutions PK', 'Air conditioning filter replacement', NOW(), NOW(), NOW()),
('ELEC002', 'Dawlance', 'MCB 20 Amp', 'Electric', 1200.00, 150, 60, 90, 25, 'Islamabad Electrical Store', 'Miniature Circuit Breaker 20 Ampere', NOW(), NOW(), NOW()),
('PLUMB002', 'A-One', 'Water Tap Mixer', 'Plumbing', 3500.00, 80, 25, 55, 15, 'Faisalabad Hardware', 'Single lever mixer tap', NOW(), NOW(), NOW()),
('BUILD001', 'Cement', 'Portland Cement 50kg', 'Building & Maintenance', 850.00, 500, 200, 300, 50, 'Lucky Cement Distributors', 'Portland cement bag 50kg', NOW(), NOW(), NOW()),
('IT001', 'TP-Link', 'Network Cable Cat6', 'IT Support', 1200.00, 60, 20, 40, 10, 'Tech Solutions Lahore', 'Ethernet cable Cat6, 5 meters', NOW(), NOW(), NOW());

-- ============================================
-- 11. COMPLAINTS TABLE (assuming client_id 1-7, assigned_employee_id 1-7, spare_id 1-7)
-- ============================================
INSERT INTO `complaints` (`title`, `client_id`, `city`, `sector`, `category`, `department`, `description`, `status`, `assigned_employee_id`, `priority`, `spare_id`, `spare_quantity`, `created_at`, `updated_at`) VALUES
('Power Outage in Sector G-6', 1, 'Islamabad', 'Sector G-6', 'Electric', 'Electrical', 'No electricity since morning, affecting entire sector', 'in_progress', 1, 'high', 1, 2, NOW(), NOW()),
('Water Leakage in Model Town', 2, 'Lahore', 'Model Town', 'Plumbing', 'Plumbing', 'Water leaking from main pipeline', 'assigned', 2, 'urgent', 2, 5, NOW(), NOW()),
('AC Not Cooling in Clifton', 3, 'Karachi', 'Clifton', 'HVAC', 'HVAC', 'Air conditioning unit not providing cooling', 'new', 3, 'medium', 3, 1, NOW(), NOW()),
('Wall Cracks in Cantonment', 4, 'Rawalpindi', 'Cantonment Area', 'Building & Maintenance', 'Building & Maintenance', 'Cracks appearing on main wall', 'assigned', 4, 'medium', 6, 10, NOW(), NOW()),
('Internet Connectivity Issue', 5, 'Faisalabad', 'Jinnah Road', 'IT Support', 'IT Support', 'Slow internet connection and frequent disconnections', 'in_progress', 5, 'high', 7, 1, NOW(), NOW()),
('Electrical Short Circuit', 6, 'Multan', 'Cantonment', 'Electric', 'Electrical', 'Short circuit in main electrical panel', 'new', 1, 'urgent', 4, 1, NOW(), NOW()),
('Blocked Drainage System', 7, 'Peshawar', 'Phase 1', 'Plumbing', 'Plumbing', 'Drainage blockage causing water accumulation', 'assigned', 2, 'high', 2, 3, NOW(), NOW());

-- ============================================
-- 12. ROLE_PERMISSIONS TABLE (assuming role_id 1-7)
-- ============================================
INSERT INTO `role_permissions` (`role_id`, `module_name`, `created_at`, `updated_at`) VALUES
(1, 'dashboard', NOW(), NOW()),
(1, 'complaints', NOW(), NOW()),
(1, 'reports', NOW(), NOW()),
(2, 'dashboard', NOW(), NOW()),
(2, 'complaints', NOW(), NOW()),
(2, 'clients', NOW(), NOW()),
(3, 'dashboard', NOW(), NOW());

-- Note: You may need to add more permissions for other roles as per your requirements

-- ============================================
-- 13. SLA_RULES TABLE (assuming notify_to user_id 1-7)
-- ============================================
INSERT INTO `sla_rules` (`complaint_type`, `priority`, `max_response_time`, `max_resolution_time`, `notify_to`, `status`, `created_at`, `updated_at`) VALUES
('Electric', 'urgent', 2, 24, 1, 'active', NOW(), NOW()),
('Electric', 'high', 4, 48, 1, 'active', NOW(), NOW()),
('Plumbing', 'urgent', 3, 36, 2, 'active', NOW(), NOW()),
('Plumbing', 'high', 6, 72, 2, 'active', NOW(), NOW()),
('HVAC', 'medium', 8, 96, 3, 'active', NOW(), NOW()),
('Building & Maintenance', 'high', 12, 120, 4, 'active', NOW(), NOW()),
('IT Support', 'medium', 4, 48, 5, 'active', NOW(), NOW());

-- ============================================
-- END OF SQL INSERT QUERIES
-- ============================================
-- Note: Foreign key constraints may require tables to be populated in order
-- Suggested order: roles -> cities -> sectors -> designations -> 
--                 users -> complaint_categories -> complaint_titles -> clients -> 
--                 employees -> spares -> complaints -> role_permissions -> sla_rules

