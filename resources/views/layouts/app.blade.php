<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'PharmaPilot') }} - @yield('title', __('messages.dashboard'))</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            /* Light Mode Colors */
            --primary-pink: #FFD6E0;
            --secondary-pink: #FFB7C5;
            --cream-white: #FFF9FB;
            --lavender: #E6E6FA;
            --rose-gold: #E0BFB8;
            --delicate-gold: #F0D9B5;
            --text-dark: #4A4A4A;
            --text-muted: #6B7280;
            --bg-overlay: rgba(255, 214, 224, 0.1);
            --card-bg: rgba(255, 249, 251, 0.8);
            --sidebar-bg: linear-gradient(180deg, #FFD6E0 0%, #E6E6FA 100%);
            --navbar-bg: linear-gradient(135deg, #FFB7C5, #E0BFB8);
            
            /* Shadow and Effects */
            --shadow-soft: 0 4px 20px rgba(255, 182, 193, 0.15);
            --shadow-hover: 0 8px 30px rgba(255, 182, 193, 0.25);
            --blur-effect: blur(10px);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        [data-theme="dark"] {
            /* Dark Mode Colors */
            --primary-pink: #7A4E6D;
            --secondary-pink: #B37B89;
            --cream-white: #1A1A1A;
            --lavender: #2D2A2E;
            --rose-gold: #E8C4A0;
            --delicate-gold: #D4AF8F;
            --text-dark: #F8F4F0;
            --text-muted: #D0C4B8;
            --bg-overlay: rgba(122, 78, 109, 0.2);
            --card-bg: rgba(35, 32, 38, 0.95);
            --sidebar-bg: linear-gradient(180deg, #2D2A2E 0%, #1A1A1A 60%, #2D2A2E 100%);
            --navbar-bg: linear-gradient(135deg, #B37B89, #E8C4A0);
            
            /* Dark mode shadows with pink glow */
            --shadow-soft: 0 4px 20px rgba(255, 182, 193, 0.15);
            --shadow-hover: 0 8px 40px rgba(255, 182, 193, 0.25);
        }
        
        * {
            transition: var(--transition);
        }
        
        body {
            font-family: 'Poppins', 'Nunito', sans-serif;
            padding-top: 70px;
            background: var(--cream-white);
            color: var(--text-dark);
            font-weight: 400;
            line-height: 1.6;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255, 214, 224, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(230, 230, 250, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255, 183, 197, 0.2) 0%, transparent 50%);
            z-index: -1;
            pointer-events: none;
        }
        
        [data-theme="dark"] body::before {
            background: 
                radial-gradient(circle at 20% 80%, rgba(122, 78, 109, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(58, 46, 58, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(179, 123, 137, 0.15) 0%, transparent 50%);
        }
        
        /* Theme Toggle Button */
        .theme-toggle {
            background: var(--rose-gold);
            border: none;
            border-radius: 50px;
            padding: 8px 16px;
            color: var(--text-dark);
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            backdrop-filter: var(--blur-effect);
            box-shadow: var(--shadow-soft);
        }
        
        .theme-toggle:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
            background: var(--delicate-gold);
        }
        
        /* Navbar Styling */
        .navbar {
            background: var(--navbar-bg) !important;
            backdrop-filter: var(--blur-effect);
            box-shadow: var(--shadow-soft);
            border: none;
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-dark) !important;
            text-shadow: 0 2px 10px rgba(255, 255, 255, 0.3);
        }
        
        .navbar-nav .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            border-radius: 25px;
            padding: 0.5rem 1rem !important;
            transition: var(--transition);
        }
        
        .navbar-nav .nav-link:hover {
            background: var(--bg-overlay);
            transform: translateY(-1px);
        }
        
        /* Sidebar Styling */
        .sidebar {
            position: fixed;
            top: 70px;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 1rem 0;
            background: var(--sidebar-bg);
            backdrop-filter: var(--blur-effect);
            box-shadow: var(--shadow-soft);
            border-right: 1px solid var(--bg-overlay);
            overflow-y: auto;
            width: 250px;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: var(--rose-gold);
            border-radius: 3px;
        }
        
        .sidebar .nav-link {
            color: var(--text-dark);
            padding: 1rem 1.5rem;
            font-weight: 500;
            border-radius: 0 25px 25px 0;
            margin: 0.25rem 0;
            margin-right: 1rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .sidebar .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, var(--bg-overlay), transparent);
            transition: var(--transition);
        }
        
        .sidebar .nav-link:hover::before {
            left: 100%;
        }
        
        .sidebar .nav-link:hover {
            color: var(--text-dark);
            background: var(--card-bg);
            transform: translateX(5px);
            box-shadow: var(--shadow-soft);
        }
        
        .sidebar .nav-link.active {
            color: var(--text-dark) !important;
            background: var(--card-bg) !important;
            box-shadow: var(--shadow-hover) !important;
            border-left: 4px solid var(--rose-gold) !important;
        }
        
        /* Fix sidebar active state and button colors */
        .sidebar .nav-link:focus,
        .sidebar .nav-link:active,
        .sidebar .nav-link.show {
            color: var(--text-dark) !important;
            background: var(--card-bg) !important;
            border-left: 4px solid var(--rose-gold) !important;
            box-shadow: var(--shadow-hover) !important;
        }
        
        /* Ensure sidebar buttons are not too dark in light mode */
        [data-theme="light"] .sidebar .nav-link {
            color: var(--text-dark) !important;
        }
        
        [data-theme="light"] .sidebar .nav-link:hover {
            color: var(--text-dark) !important;
            background: var(--card-bg) !important;
        }
        
        .sidebar .nav-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        /* Main Content */
        .content {
            margin-left: 250px;
            padding: 2rem;
            min-height: calc(100vh - 70px);
        }
        
        /* Cards with Frosted Glass Effect */
        .card {
            background: var(--card-bg) !important;
            backdrop-filter: var(--blur-effect);
            border: 1px solid var(--bg-overlay);
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: var(--transition);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        
        .card-header {
            background: var(--bg-overlay) !important;
            border-bottom: 1px solid var(--bg-overlay);
            font-weight: 600;
            color: var(--text-dark);
            padding: 1.5rem;
            border-radius: 20px 20px 0 0 !important;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        /* Buttons */
        .btn {
            border-radius: 25px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: var(--transition);
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-pink), var(--rose-gold)) !important;
            color: var(--text-dark) !important;
            box-shadow: var(--shadow-soft);
            border: none !important;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
            background: linear-gradient(135deg, var(--rose-gold), var(--delicate-gold)) !important;
            color: var(--text-dark) !important;
        }
        
        .btn-secondary {
            background: var(--lavender) !important;
            color: var(--text-dark) !important;
            box-shadow: var(--shadow-soft);
            border: none !important;
        }
        
        .btn-secondary:hover {
            background: var(--bg-overlay) !important;
            color: var(--text-dark) !important;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #B8E6B8, var(--delicate-gold)) !important;
            color: var(--text-dark) !important;
            border: none !important;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #A0D8A0, var(--rose-gold)) !important;
            color: var(--text-dark) !important;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #FFB3BA, var(--secondary-pink)) !important;
            color: var(--text-dark) !important;
            border: none !important;
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #FF9BA3, #FF8A9B) !important;
            color: var(--text-dark) !important;
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, var(--delicate-gold), #F4D03F) !important;
            color: var(--text-dark) !important;
            border: none !important;
        }
        
        .btn-warning:hover {
            background: linear-gradient(135deg, #F7DC6F, var(--delicate-gold)) !important;
            color: var(--text-dark) !important;
            transform: translateY(-2px);
        }
        
        .btn-info {
            background: linear-gradient(135deg, #85C1E9, var(--lavender)) !important;
            color: var(--text-dark) !important;
            border: none !important;
        }
        
        .btn-info:hover {
            background: linear-gradient(135deg, #5DADE2, #AED6F1) !important;
            color: var(--text-dark) !important;
            transform: translateY(-2px);
        }
        
        .btn-light {
            background: var(--card-bg) !important;
            color: var(--text-dark) !important;
            border: 1px solid var(--bg-overlay) !important;
        }
        
        .btn-light:hover {
            background: var(--bg-overlay) !important;
            color: var(--text-dark) !important;
            transform: translateY(-2px);
        }
        
        .btn-dark {
            background: var(--text-dark) !important;
            color: var(--cream-white) !important;
            border: none !important;
        }
        
        .btn-dark:hover {
            background: var(--text-muted) !important;
            color: var(--cream-white) !important;
            transform: translateY(-2px);
        }
        
        /* Consistent Action Button Styling */
        .btn-sm {
            padding: 0.5rem 1rem !important;
            font-size: 0.875rem !important;
            border-radius: 20px !important;
            font-weight: 500 !important;
            min-width: 80px !important;
            text-align: center !important;
        }
        
        /* View Button - Info Style with Pink Theme */
        .btn-info, .btn-outline-info {
            background: linear-gradient(135deg, #85C1E9, var(--lavender)) !important;
            color: var(--text-dark) !important;
            border: none !important;
            border-radius: 20px !important;
        }
        
        .btn-info:hover, .btn-outline-info:hover {
            background: linear-gradient(135deg, #5DADE2, #AED6F1) !important;
            color: var(--text-dark) !important;
            transform: translateY(-2px) !important;
            box-shadow: var(--shadow-hover) !important;
        }
        
        /* Edit Button - Warning Style with Pink Theme */
        .btn-warning, .btn-outline-warning {
            background: linear-gradient(135deg, var(--delicate-gold), #F4D03F) !important;
            color: var(--text-dark) !important;
            border: none !important;
            border-radius: 20px !important;
        }
        
        .btn-warning:hover, .btn-outline-warning:hover {
            background: linear-gradient(135deg, #F7DC6F, var(--delicate-gold)) !important;
            color: var(--text-dark) !important;
            transform: translateY(-2px) !important;
            box-shadow: var(--shadow-hover) !important;
        }
        
        /* Delete Button - Danger Style with Pink Theme */
        .btn-danger, .btn-outline-danger {
            background: linear-gradient(135deg, #FFB3BA, var(--secondary-pink)) !important;
            color: var(--text-dark) !important;
            border: none !important;
            border-radius: 20px !important;
        }
        
        .btn-danger:hover, .btn-outline-danger:hover {
            background: linear-gradient(135deg, #FF9BA3, #FF8A9B) !important;
            color: var(--text-dark) !important;
            transform: translateY(-2px) !important;
            box-shadow: var(--shadow-hover) !important;
        }
        
        /* Sidebar Button Consistency */
        .sidebar .btn {
            width: 100% !important;
            margin: 0.5rem 0 !important;
            padding: 0.75rem 1rem !important;
            border-radius: 20px !important;
            font-weight: 500 !important;
            transition: var(--transition) !important;
        }
        
        .sidebar .btn-primary {
            background: linear-gradient(135deg, var(--secondary-pink), var(--rose-gold)) !important;
            color: var(--text-dark) !important;
            border: none !important;
        }
        
        .sidebar .btn-primary:hover {
            background: linear-gradient(135deg, var(--rose-gold), var(--delicate-gold)) !important;
            transform: translateX(5px) !important;
            box-shadow: var(--shadow-hover) !important;
        }
        
        /* Tables */
        .table {
            background: var(--card-bg);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
        }
        
        .table thead th {
            background: var(--bg-overlay);
            border: none;
            font-weight: 600;
            color: var(--text-dark);
            padding: 1rem;
        }
        
        .table tbody td {
            border: none;
            padding: 1rem;
            vertical-align: middle;
        }
        
        .table tbody tr {
            border-bottom: 1px solid var(--bg-overlay);
            transition: var(--transition);
        }
        
        .table tbody tr:hover {
            background: var(--bg-overlay);
            transform: scale(1.01);
        }
        
        /* Dropdown Menus */
        .dropdown-menu {
            background: var(--card-bg);
            backdrop-filter: var(--blur-effect);
            border: 1px solid var(--bg-overlay);
            border-radius: 15px;
            box-shadow: var(--shadow-hover);
            padding: 0.5rem;
        }
        
        .dropdown-item {
            border-radius: 10px;
            margin: 0.25rem 0;
            transition: var(--transition);
            color: var(--text-dark);
        }
        
        .dropdown-item:hover {
            background: var(--bg-overlay);
            color: var(--text-dark);
            transform: translateX(5px);
        }
        
        /* Alerts */
        .alert {
            border: none;
            border-radius: 15px;
            backdrop-filter: var(--blur-effect);
            box-shadow: var(--shadow-soft);
        }
        
        .alert-success {
            background: linear-gradient(135deg, rgba(184, 230, 184, 0.8), var(--card-bg));
            color: var(--text-dark);
        }
        
        .alert-danger {
            background: linear-gradient(135deg, rgba(255, 179, 186, 0.8), var(--card-bg));
            color: var(--text-dark);
        }
        
        /* Status Classes - Enhanced for Dark Mode */
        .low-stock {
            background: linear-gradient(135deg, rgba(255, 243, 205, 0.8), var(--card-bg)) !important;
            border-left: 4px solid var(--delicate-gold) !important;
            color: var(--text-dark) !important;
        }
        
        [data-theme="dark"] .low-stock {
            background: linear-gradient(135deg, rgba(240, 217, 181, 0.3), var(--card-bg)) !important;
            border-left: 4px solid var(--delicate-gold) !important;
            color: var(--text-dark) !important;
        }
        
        .near-expiry {
            background: linear-gradient(135deg, rgba(248, 215, 218, 0.8), var(--card-bg)) !important;
            border-left: 4px solid var(--secondary-pink) !important;
            color: var(--text-dark) !important;
        }
        
        [data-theme="dark"] .near-expiry {
            background: linear-gradient(135deg, rgba(122, 78, 109, 0.15), var(--card-bg)) !important;
            border-left: 4px solid rgba(224, 191, 184, 0.6) !important;
            color: var(--text-dark) !important;
        }
        
        /* Expired Product Styling */
        .expired {
            background: linear-gradient(135deg, rgba(255, 179, 186, 0.9), var(--card-bg)) !important;
            border-left: 4px solid #FF6B7A !important;
            color: var(--text-dark) !important;
        }
        
        [data-theme="dark"] .expired {
            background: linear-gradient(135deg, rgba(122, 78, 109, 0.2), var(--card-bg)) !important;
            border-left: 4px solid rgba(224, 191, 184, 0.5) !important;
            color: var(--text-dark) !important;
        }
        
        /* Table Button Alignment Fix */
        .table .action-buttons,
        .table td:last-child {
            text-align: center !important;
            vertical-align: middle !important;
        }
        
        .table .action-buttons .btn {
            margin: 0 2px !important;
            min-width: 70px !important;
            display: inline-block !important;
        }
        
        /* Ensure all action buttons have same height and alignment */
        .btn-group-sm > .btn, .btn-sm {
            padding: 0.4rem 0.8rem !important;
            font-size: 0.8rem !important;
            line-height: 1.4 !important;
            border-radius: 18px !important;
        }
        
        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            padding: 0.25rem 0.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-pink), var(--rose-gold));
            color: var(--text-dark);
            font-size: 0.75rem;
            font-weight: 600;
            box-shadow: var(--shadow-soft);
        }
        
        /* Form Controls */
        .form-control, .form-select {
            background: var(--card-bg);
            border: 1px solid var(--bg-overlay);
            border-radius: 15px;
            color: var(--text-dark);
            padding: 0.75rem 1rem;
            transition: var(--transition);
        }
        
        .form-control:focus, .form-select:focus {
            background: var(--card-bg) !important;
            border-color: var(--rose-gold) !important;
            box-shadow: 0 0 0 0.2rem rgba(224, 191, 184, 0.25) !important;
            color: var(--text-dark) !important;
        }
        
        .form-control::placeholder {
            color: var(--text-muted) !important;
            opacity: 0.8;
        }
        
        /* Input Group Styling */
        .input-group-text {
            background: var(--bg-overlay) !important;
            border: 1px solid var(--bg-overlay) !important;
            color: var(--text-dark) !important;
        }
        
        /* Small Text Elements */
        small, .small {
            color: var(--text-muted) !important;
        }
        
        /* Help Text */
        .form-text {
            color: var(--text-muted) !important;
        }
        
        /* Labels */
        label {
            color: var(--text-dark) !important;
            font-weight: 500;
        }
        
        /* Legend */
        legend {
            color: var(--text-dark) !important;
        }
        
        /* Footer */
        .footer {
            background: var(--card-bg);
            backdrop-filter: var(--blur-effect);
            border-top: 1px solid var(--bg-overlay);
            margin-top: 3rem;
            padding: 1.5rem 0;
            color: var(--text-muted);
        }
        
        /* Navbar Toggler Custom Style */
        .navbar-toggler {
            border: none !important;
            background: var(--bg-overlay) !important;
            border-radius: 10px;
            padding: 0.5rem;
            transition: var(--transition);
        }
        
        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(224, 191, 184, 0.25) !important;
        }
        
        .navbar-toggler-icon {
            background-image: none !important;
            position: relative;
            width: 20px;
            height: 20px;
        }
        
        .navbar-toggler-icon::before,
        .navbar-toggler-icon::after,
        .navbar-toggler-icon {
            content: '';
            display: block;
            width: 100%;
            height: 2px;
            background: var(--text-dark);
            border-radius: 1px;
            transition: var(--transition);
        }
        
        .navbar-toggler-icon::before {
            transform: translateY(-6px);
        }
        
        .navbar-toggler-icon::after {
            transform: translateY(4px);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: var(--transition);
                z-index: 1050;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .navbar-toggler {
                order: -1;
                margin-right: auto;
            }
        }
        
        /* Enhanced Text Contrast and Icon Styling */
        .text-primary {
            color: var(--text-dark) !important;
        }
        
        .text-secondary {
            color: var(--text-muted) !important;
        }
        
        .text-light {
            color: var(--text-muted) !important;
        }
        
        .text-white {
            color: var(--text-dark) !important;
        }
        
        .bg-primary {
            background: var(--primary-pink) !important;
            color: var(--text-dark) !important;
        }
        
        .bg-secondary {
            background: var(--secondary-pink) !important;
            color: var(--text-dark) !important;
        }
        
        /* Fix "View All" and similar link buttons */
        .btn-link {
            background: linear-gradient(135deg, var(--secondary-pink), var(--rose-gold)) !important;
            color: var(--text-dark) !important;
            text-decoration: none !important;
            border: none !important;
            border-radius: 20px !important;
            padding: 0.5rem 1rem !important;
            font-weight: 500 !important;
            transition: var(--transition) !important;
        }
        
        .btn-link:hover {
            background: linear-gradient(135deg, var(--rose-gold), var(--delicate-gold)) !important;
            color: var(--text-dark) !important;
            transform: translateY(-2px) !important;
            box-shadow: var(--shadow-hover) !important;
        }
        
        /* Fix blue links and make them pink */
        a.btn, .btn-outline-primary {
            background: linear-gradient(135deg, var(--secondary-pink), var(--rose-gold)) !important;
            color: var(--text-dark) !important;
            border: none !important;
            border-radius: 20px !important;
        }
        
        a.btn:hover, .btn-outline-primary:hover {
            background: linear-gradient(135deg, var(--rose-gold), var(--delicate-gold)) !important;
            color: var(--text-dark) !important;
            transform: translateY(-2px) !important;
        }
        
        /* Icon Consistency */
        .fas, .far, .fab {
            color: inherit;
        }
        
        .navbar .fas, .navbar .far {
            color: var(--text-dark) !important;
        }
        
        .sidebar .fas, .sidebar .far {
            color: var(--text-dark) !important;
        }
        
        /* Enhanced Table Text Contrast */
        .table {
            color: var(--text-dark) !important;
        }
        
        .table thead th {
            color: var(--text-dark) !important;
            font-weight: 600;
        }
        
        .table tbody td {
            color: var(--text-dark) !important;
        }
        
        .table tbody tr:hover {
            color: var(--text-dark) !important;
        }
        
        /* Card Text Enhancement */
        .card-title {
            color: var(--text-dark) !important;
            font-weight: 600;
        }
        
        .card-text {
            color: var(--text-dark) !important;
        }
        
        .card-subtitle {
            color: var(--text-muted) !important;
        }
        
        /* Enhanced Button Text */
        .btn {
            color: var(--text-dark) !important;
            font-weight: 500;
        }
        
        .btn:hover {
            color: var(--text-dark) !important;
        }
        
        .btn:focus {
            color: var(--text-dark) !important;
        }
        
        /* Badge Styling */
        .badge {
            background: var(--rose-gold) !important;
            color: var(--text-dark) !important;
            font-weight: 500;
            border-radius: 15px !important;
            padding: 0.4rem 0.8rem !important;
            font-size: 0.75rem !important;
        }
        
        /* Status Badge Variants */
        .badge-expired {
            background: linear-gradient(135deg, #FF6B7A, var(--secondary-pink)) !important;
            color: var(--text-dark) !important;
        }
        
        .badge-near-expiry {
            background: linear-gradient(135deg, var(--delicate-gold), #F4D03F) !important;
            color: var(--text-dark) !important;
        }
        
        .badge-low-stock {
            background: linear-gradient(135deg, #FFB366, var(--delicate-gold)) !important;
            color: var(--text-dark) !important;
        }
        
        .badge-in-stock {
            background: linear-gradient(135deg, #90EE90, #B8E6B8) !important;
            color: var(--text-dark) !important;
        }
        
        [data-theme="dark"] .badge-expired {
            background: linear-gradient(135deg, rgba(224, 191, 184, 0.8), rgba(179, 123, 137, 0.8)) !important;
            color: var(--text-dark) !important;
        }
        
        [data-theme="dark"] .badge-near-expiry {
            background: linear-gradient(135deg, rgba(212, 175, 143, 0.8), rgba(196, 159, 122, 0.8)) !important;
            color: var(--text-dark) !important;
        }
        
        [data-theme="dark"] .badge-low-stock {
            background: linear-gradient(135deg, rgba(212, 175, 143, 0.6), rgba(240, 217, 181, 0.6)) !important;
            color: var(--text-dark) !important;
        }
        
        /* Enhanced Link Styling */
        a {
            color: var(--rose-gold);
            text-decoration: none;
            transition: var(--transition);
        }
        
        a:hover {
            color: var(--delicate-gold);
            text-decoration: none;
        }
        
        /* List Group Items */
        .list-group-item {
            background: var(--card-bg) !important;
            border: 1px solid var(--bg-overlay) !important;
            color: var(--text-dark) !important;
        }
        
        .list-group-item:hover {
            background: var(--bg-overlay) !important;
            color: var(--text-dark) !important;
        }
        
        /* Progress Bar Styling */
        .progress {
            background: var(--bg-overlay) !important;
            border-radius: 10px;
        }
        
        .progress-bar {
            background: linear-gradient(135deg, var(--secondary-pink), var(--rose-gold)) !important;
        }
        
        /* Modal Styling */
        .modal-content {
            background: var(--card-bg) !important;
            border: 1px solid var(--bg-overlay) !important;
            border-radius: 20px;
            color: var(--text-dark) !important;
        }
        
        .modal-header {
            border-bottom: 1px solid var(--bg-overlay) !important;
            color: var(--text-dark) !important;
        }
        
        .modal-title {
            color: var(--text-dark) !important;
        }
        
        .modal-footer {
            border-top: 1px solid var(--bg-overlay) !important;
        }
        
        /* Tooltip Styling */
        .tooltip {
            --bs-tooltip-bg: var(--card-bg);
            --bs-tooltip-color: var(--text-dark);
        }
        
        /* Breadcrumb Styling */
        .breadcrumb {
            background: var(--bg-overlay) !important;
            border-radius: 15px;
        }
        
        .breadcrumb-item {
            color: var(--text-muted) !important;
        }
        
        .breadcrumb-item.active {
            color: var(--text-dark) !important;
        }
        
        .breadcrumb-item a {
            color: var(--rose-gold) !important;
        }
        
        /* Pagination Styling */
        .page-link {
            background: var(--card-bg) !important;
            border: 1px solid var(--bg-overlay) !important;
            color: var(--text-dark) !important;
            border-radius: 10px;
            margin: 0 2px;
        }
        
        .page-link:hover {
            background: var(--bg-overlay) !important;
            color: var(--text-dark) !important;
        }
        
        .page-item.active .page-link {
            background: var(--rose-gold) !important;
            border-color: var(--rose-gold) !important;
            color: var(--text-dark) !important;
        }
        
        /* DataTable Styling */
        .dataTables_wrapper {
            color: var(--text-dark) !important;
        }
        
        .dataTables_filter input {
            background: var(--card-bg) !important;
            border: 1px solid var(--bg-overlay) !important;
            border-radius: 15px;
            color: var(--text-dark) !important;
            padding: 0.5rem 1rem;
        }
        
        .dataTables_length select {
            background: var(--card-bg) !important;
            border: 1px solid var(--bg-overlay) !important;
            border-radius: 10px;
            color: var(--text-dark) !important;
        }
        
        .dataTables_info {
            color: var(--text-muted) !important;
        }
        
        /* Override Bootstrap Button Defaults */
        .btn:not(:disabled):not(.disabled) {
            cursor: pointer;
        }
        
        .btn:focus {
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(224, 191, 184, 0.25) !important;
        }
        
        .btn.disabled, .btn:disabled {
            opacity: 0.6;
            transform: none !important;
        }
        
        /* Button Group Consistency */
        .btn-group .btn {
            border-radius: 20px !important;
            margin: 0 2px !important;
        }
        
        .btn-group .btn:first-child {
            border-top-left-radius: 20px !important;
            border-bottom-left-radius: 20px !important;
        }
        
        .btn-group .btn:last-child {
            border-top-right-radius: 20px !important;
            border-bottom-right-radius: 20px !important;
        }
        
        /* Action Button Container */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            justify-content: center;
        }
        
        .action-buttons .btn {
            flex: 1;
            max-width: 90px;
        }
        
        /* Fix Bootstrap Nav Tabs to Pink Theme */
        .nav-tabs {
            border-bottom: 2px solid var(--bg-overlay) !important;
        }
        
        .nav-tabs .nav-link {
            background: var(--card-bg) !important;
            border: 1px solid var(--bg-overlay) !important;
            color: var(--text-muted) !important;
            border-radius: 15px 15px 0 0 !important;
            margin-right: 0.25rem !important;
            padding: 0.75rem 1.5rem !important;
            font-weight: 500 !important;
            transition: var(--transition) !important;
        }
        
        .nav-tabs .nav-link:hover {
            background: var(--bg-overlay) !important;
            color: var(--text-dark) !important;
            border-color: var(--rose-gold) !important;
        }
        
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, var(--secondary-pink), var(--rose-gold)) !important;
            color: var(--text-dark) !important;
            border-color: var(--rose-gold) !important;
            border-bottom-color: transparent !important;
        }
        
        .nav-tabs .nav-link.active:hover {
            background: linear-gradient(135deg, var(--rose-gold), var(--delicate-gold)) !important;
            color: var(--text-dark) !important;
        }
        
        /* Tab Content Styling */
        .tab-content {
            background: var(--card-bg) !important;
            border: 1px solid var(--bg-overlay) !important;
            border-radius: 0 15px 15px 15px !important;
            padding: 2rem !important;
        }
        
        /* Text Selection Styling */
        ::selection {
            background: var(--secondary-pink) !important;
            color: var(--text-dark) !important;
        }
        
        ::-moz-selection {
            background: var(--secondary-pink) !important;
            color: var(--text-dark) !important;
        }
        
        /* Input Selection Styling */
        .form-control::selection {
            background: var(--rose-gold) !important;
            color: var(--text-dark) !important;
        }
        
        .form-control::-moz-selection {
            background: var(--rose-gold) !important;
            color: var(--text-dark) !important;
        }
        
        /* Select Dropdown Styling */
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23%234A4A4A' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e") !important;
        }
        
        .form-select:focus {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23%23E0BFB8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e") !important;
        }
        
        .form-select option {
            background: var(--card-bg) !important;
            color: var(--text-dark) !important;
        }
        
        .form-select option:hover,
        .form-select option:focus,
        .form-select option:checked {
            background: var(--bg-overlay) !important;
            color: var(--text-dark) !important;
        }
        
        /* Highlighted/Selected Options */
        .form-select option:checked {
            background: linear-gradient(135deg, var(--secondary-pink), var(--bg-overlay)) !important;
            color: var(--text-dark) !important;
        }
        
        /* Multi-select styling */
        .form-select[multiple] option:checked {
            background: var(--rose-gold) !important;
            color: var(--text-dark) !important;
        }
        
        /* Input highlight/focus selections */
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="number"]:focus,
        input[type="tel"]:focus,
        input[type="url"]:focus,
        textarea:focus {
            background: var(--card-bg) !important;
            color: var(--text-dark) !important;
        }
        
        /* Autocomplete dropdown styling */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 1000px var(--card-bg) inset !important;
            -webkit-text-fill-color: var(--text-dark) !important;
            border-color: var(--rose-gold) !important;
        }
        
        /* Table row selection */
        .table tbody tr.selected {
            background: var(--bg-overlay) !important;
            color: var(--text-dark) !important;
        }
        
        .table tbody tr.selected:hover {
            background: var(--secondary-pink) !important;
            color: var(--text-dark) !important;
        }
        
        /* List item selection */
        .list-group-item.active {
            background: linear-gradient(135deg, var(--secondary-pink), var(--rose-gold)) !important;
            border-color: var(--rose-gold) !important;
            color: var(--text-dark) !important;
        }
        
        /* Button active/pressed states */
        .btn:active,
        .btn.active {
            background: var(--bg-overlay) !important;
            border-color: var(--rose-gold) !important;
            color: var(--text-dark) !important;
        }
        
        /* Checkbox and Radio Selection */
        .form-check-input:checked {
            background-color: var(--rose-gold) !important;
            border-color: var(--rose-gold) !important;
        }
        
        .form-check-input:focus {
            border-color: var(--rose-gold) !important;
            box-shadow: 0 0 0 0.2rem rgba(224, 191, 184, 0.25) !important;
        }
        
        /* Range slider styling */
        .form-range::-webkit-slider-thumb {
            background: var(--rose-gold) !important;
        }
        
        .form-range::-moz-range-thumb {
            background: var(--rose-gold) !important;
        }
        
        .form-range::-webkit-slider-track {
            background: var(--bg-overlay) !important;
        }
        
        .form-range::-moz-range-track {
            background: var(--bg-overlay) !important;
        }
        
        /* Animation Classes */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: var(--shadow-soft); }
            50% { box-shadow: var(--shadow-hover); }
            100% { box-shadow: var(--shadow-soft); }
        }
    </style>
    
    @yield('styles')
</head>
<body data-theme="light">
    <!-- Fixed Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-pills me-2"></i>
                PharmaPilot
            </a>
            
            <div class="d-flex align-items-center">
                <!-- Theme Toggle -->
                <button class="theme-toggle me-3" onclick="toggleTheme()" title="Toggle Theme">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- Language Switcher -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-globe me-1"></i>
                            @if(App::getLocale() == 'en')
                                🇺🇸 {{ __('messages.english') }}
                            @elseif(App::getLocale() == 'fr')
                                🇫🇷 {{ __('messages.french') }}
                            @elseif(App::getLocale() == 'ar')
                                🇸🇦 {{ __('messages.arabic') }}
                            @endif
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                            <li>
                                <a class="dropdown-item {{ App::getLocale() == 'en' ? 'active' : '' }}" href="{{ route('language.switch', 'en') }}">
                                    🇺🇸 {{ __('messages.english') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ App::getLocale() == 'fr' ? 'active' : '' }}" href="{{ route('language.switch', 'fr') }}">
                                    🇫🇷 {{ __('messages.french') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ App::getLocale() == 'ar' ? 'active' : '' }}" href="{{ route('language.switch', 'ar') }}">
                                    🇸🇦 {{ __('messages.arabic') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                    @include('components.notification-dropdown')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('users.show', Auth::id()) }}">
                                    <i class="fas fa-user me-2"></i> {{ __('messages.profile') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('users.change-password', Auth::id()) }}">
                                    <i class="fas fa-key me-2"></i> {{ __('messages.change_password') }}
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i> {{ __('messages.logout') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="fas fa-fw fa-tachometer-alt"></i>
                                <span>{{ __('messages.dashboard') }}</span>
                            </a>
                        </li>
                        <li class="nav-item dropdown {{ request()->routeIs('sales.*') ? 'show' : '' }}">
                            <a class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}" href="{{ route('pos.index') }}">
                                <i class="fas fa-fw fa-cash-register"></i>
                                <span>{{ __('messages.pos_system') }}</span>
                            </a>
                            <ul class="dropdown-menu bg-dark" aria-labelledby="salesDropdown">
    <li>
        <a class="dropdown-item text-white {{ request()->routeIs('sales.index') ? 'active' : '' }}" href="{{ route('sales.index') }}">
            <i class="fas fa-list me-2"></i> {{ __('messages.sales_list') }}
        </a>
    </li>

                                <li>
                                    <a class="dropdown-item text-white {{ request()->routeIs('pos.*') ? 'active' : '' }}" href="{{ route('pos.index') }}">
                                        <i class="fas fa-cash-register me-2"></i> {{ __('messages.pos_system') }}
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">
        <i class="fas fa-fw fa-list"></i>
        <span>{{ __('messages.sales_list') }}</span>
    </a>
</li>
<li class="nav-item dropdown {{ request()->routeIs('products.*') || request()->routeIs('inventory') || request()->routeIs('suppliers.*') ? 'show' : '' }}">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('products.*') || request()->routeIs('inventory') || request()->routeIs('suppliers.*') ? 'active' : '' }}" href="#" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="{{ request()->routeIs('products.*') || request()->routeIs('inventory') || request()->routeIs('suppliers.*') ? 'true' : 'false' }}">
                                <i class="fas fa-fw fa-boxes"></i>
                                <span>{{ __('messages.inventory') }}</span>
                            </a>
                            <ul class="dropdown-menu bg-dark" aria-labelledby="productsDropdown">
                                <li>
                                    <a class="dropdown-item text-white {{ request()->routeIs('products.create') ? 'active' : '' }}" href="{{ route('products.create') }}">
                                        <i class="fas fa-plus-circle me-2"></i> {{ __('messages.add_product') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-white {{ request()->routeIs('inventory') ? 'active' : '' }}" href="{{ route('inventory') }}">
                                        <i class="fas fa-fw fa-box"></i>
                                        <span>{{ __('messages.products') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-white {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                                        <i class="fas fa-fw fa-truck"></i>
                                        <span>{{ __('messages.suppliers') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                                <i class="fas fa-fw fa-tags"></i>
                                <span>{{ __('messages.categories') }}</span>
                            </a>
                        </li>
                        @if(auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.sales-performance') ? 'active' : '' }}" href="{{ route('users.sales-performance') }}">
                                <i class="fas fa-fw fa-chart-line"></i>
                                <span>{{ __('messages.performance') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.index') || request()->routeIs('users.create') || request()->routeIs('users.edit') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                <i class="fas fa-fw fa-users"></i>
                                <span>{{ __('messages.users') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                                <i class="fas fa-fw fa-chart-bar"></i>
                                <span>{{ __('messages.reports') }}</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <script>document.write(new Date().getFullYear())</script> &copy; PharmaPilot.
                </div>
                <div class="col-sm-6">
                    <div class="text-sm-end d-none d-sm-block">
                        Current Locale: {{ app()->getLocale() }}
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('.datatable').DataTable({
                "responsive": true,
                "language": {
                    "search": "Filter records:",
                    "lengthMenu": "Show _MENU_ entries per page",
                    "zeroRecords": "No matching records found",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "infoEmpty": "Showing 0 to 0 of 0 entries",
                    "infoFiltered": "(filtered from _MAX_ total entries)"
                }
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
    
    <!-- Theme Toggle Script -->
    <script>
        // Theme toggle functionality
        function toggleTheme() {
            const body = document.body;
            const themeIcon = document.getElementById('theme-icon');
            const currentTheme = body.getAttribute('data-theme');
            
            if (currentTheme === 'light') {
                body.setAttribute('data-theme', 'dark');
                themeIcon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'dark');
            } else {
                body.setAttribute('data-theme', 'light');
                themeIcon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'light');
            }
            
            // Add transition class for smooth animation
            body.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            setTimeout(() => {
                body.style.transition = '';
            }, 300);
        }
        
        // Load saved theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            const body = document.body;
            const themeIcon = document.getElementById('theme-icon');
            
            body.setAttribute('data-theme', savedTheme);
            
            if (savedTheme === 'dark') {
                themeIcon.className = 'fas fa-sun';
            } else {
                themeIcon.className = 'fas fa-moon';
            }
        });
        
        // Sidebar toggle for mobile
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('show');
        }
        
        // Add mobile sidebar toggle to navbar toggler
        document.addEventListener('DOMContentLoaded', function() {
            const navbarToggler = document.querySelector('.navbar-toggler');
            if (navbarToggler) {
                navbarToggler.addEventListener('click', toggleSidebar);
            }
            
            // Add fade-in animation to cards
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('fade-in');
                }, index * 100);
            });
            
            // Add pulse animation to notification badges
            const badges = document.querySelectorAll('.notification-badge');
            badges.forEach(badge => {
                badge.classList.add('pulse');
            });
        });
        
        // Enhanced table hover effects
        document.addEventListener('DOMContentLoaded', function() {
            const tableRows = document.querySelectorAll('.table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.02)';
                    this.style.boxShadow = 'var(--shadow-hover)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.boxShadow = 'none';
                });
            });
        });
    </script>
    
    <script src="https://unpkg.com/@zxing/library@0.20.0/umd/index.min.js"></script>
    @yield('scripts')
</body>
</html>