<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session
session_start();

// Initialize the customers and tasks arrays in the session if they don't exist
if (!isset($_SESSION['customers'])) {
    $_SESSION['customers'] = [];
}
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

// Calculate task counts
$pending_count = 0;
$completed_count = 0;
foreach ($_SESSION['tasks'] as $task) {
    if ($task['status'] == 'pending') {
        $pending_count++;
    } elseif ($task['status'] == 'completed') {
        $completed_count++;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_customer'])) {
        // Validate form inputs
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);

        if (empty($name) || empty($email) || empty($phone) || empty($address)) {
            die("All fields are required.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die("Invalid email format.");
        }

        $new_customer = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'profile_pic' => 'default.jpg'
        ];

        if ($_FILES['profile_pic']['error'] == 0) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $target_file = $target_dir . basename($_FILES["profile_pic"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($imageFileType, $allowedExtensions)) {
                die("Only JPG, JPEG, PNG, and GIF files are allowed.");
            }
            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                $new_customer['profile_pic'] = basename($_FILES["profile_pic"]["name"]);
            } else {
                die("Error uploading file.");
            }
        }

        array_push($_SESSION['customers'], $new_customer);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (isset($_POST['add_task'])) {
        $task_title = trim($_POST['task_title']);
        $task_description = trim($_POST['task_description']);
        $task_status = $_POST['task_status'];
        $customer_id = $_POST['customer_id'];

        if (empty($task_title) || !isset($_SESSION['customers'][$customer_id])) {
            die("Invalid task data.");
        }

        $new_task = [
            'title' => $task_title,
            'description' => $task_description,
            'status' => $task_status,
            'customer_id' => $customer_id
        ];
        array_push($_SESSION['tasks'], $new_task);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        if ($username == 'admin' && $password == 'password') {
            $_SESSION['logged_in'] = true;
            $_SESSION['login_success'] = true; // Set a session variable for login success
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid credentials"; // Set a session variable for login error
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    } elseif (isset($_POST['signup'])) {
        $fullName = trim($_POST['fullName']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirmPassword = trim($_POST['confirmPassword']);

        if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
            die("All fields are required.");
        }
        if ($password !== $confirmPassword) {
            die("Passwords do not match.");
        }

        // Simulate saving user data (in a real application, this would be saved to a database)
        $_SESSION['users'][] = [
            'fullName' => $fullName,
            'email' => $email,
            'password' => $password
        ];

        echo "<script>alert('Sign up successful!');</script>";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Get search and sort parameters
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';

// Filter customers
$filtered_customers = [];
if ($search_term) {
    foreach ($_SESSION['customers'] as $id => $customer) {
        if (stripos($customer['name'], $search_term) !== false || stripos($customer['email'], $search_term) !== false) {
            $filtered_customers[$id] = $customer;
        }
    }
} else {
    $filtered_customers = $_SESSION['customers'];
}

// Sort customers
if ($sort == 'name_asc') {
    usort($filtered_customers, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
} elseif ($sort == 'name_desc') {
    usort($filtered_customers, function($a, $b) {
        return strcmp($b['name'], $a['name']);
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style> 
    
    body {
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-image:url("newyork-bridge_A4MOE4EVDQ.jpg");
            opacity: 0.6;
            color:white;
            
        }
        body:hover{
            opacity: 5;
            
        }
        .header {
            background-color:blue;
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5rem;
        }
        .navbar {
            background-color:green;
            color:blue;
        }
        .navbar-brand, .nav-link {
            color: #fff !important;
        }
        .card {
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: green;
            color: #fff;
        }
        .form-control {
            margin-bottom: 10px;
        }
        .profile-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }
        footer {
            background-color: #007bff;
            color: #fff;
            padding: 20px 0;
            margin-top: auto;
        }
        footer a {
            color: #fff;
            text-decoration: none;
        }
        footer a:hover {
            text-decoration: none;
            background-color: black;

        }
        .search-bar {
            margin-right: 10px;
        }
     
        /* Responsive Styles */
@media (max-width: 768px) {
    .container {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
    }

    .metrics {
        flex-direction: column;
        align-items: center;
    }

    .metric {
        width: 80%;
        margin: 10px 0;
    }
}

.customer-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px; 
}

.customer-item {
    background-color: #f8f9fa;
    border-radius: 5px;
    padding: 10px;
    text-align: center;
    width: 550px; 
    
}

.customer-photo {
    width: 100%; 
    height: auto; 
    border-radius: 5px; 
    object-fit: cover; 
}

@media (max-width: 768px) {
    .customer-grid {
        justify-content: center; 
    }

    .customer-item {
        width: 400px; 
    }

    .customer-photo {
        width: 100%;
        height: auto; 
    }
}
  #navbarNav ul li a:hover {
   
    background-color: black;
  }
  #about #about_us{
    background-color:black; 
   
  }
 #about #about_us:hover{
   
}
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>CUSTOMER RELATIONSHIP MANAGEMENT (CRM) SYSTEM</h1>
    </div>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                <li class="nav-item">
                        <a class="nav-link" href="#about">about us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#dashboard">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#customers">Customers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tasks">Tasks</a>
                    </li>
                </ul>
                <!-- Search Bar -->
                <form class="d-flex search-bar" method="GET" action="">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search customers" aria-label="Search">
                    <button class="btn btn-outline-light" type="submit">Search</button>
                </form>
                
                <!-- Login and Sign Up Buttons -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Admin
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
                        
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#signupModal">Sign Up</a></li>
                            <li class="nav-item">
                            <a class="dropdown-item" href="?logout">Logout</a>
                             </li>
                        </ul>
                    </li>
                      
                    
                </ul>
            </div>
        </div>
    </nav>

    
    <!-- Main Content -->
    <div class="container mt-5">

    <div id="customers" class="section">

                <h1>Manage your customer profiles and interactions.</h1>
               
                <div class="customer-list">
    
                    <ul class="customer-grid">
                        
                        <li class="customer-item">
                            
                            <img src="callphoto.png" alt="Customer 1" class="customer-photo">
                        </li>
                        <li class="customer-item">
                           
                            <img src="photo6.avif" alt="Customer 2" class="customer-photo">
                        </li>           
                       
                    </ul>
                </div>
            </div>

<div id="about" class="row mt-5">
        <div class="col-md-12" id="about_us">
                  <h1> ABOUT US</h1>
                <h2>CRM System Overview</h2>
           <p>Our CRM (Customer Relationship Management) platform is a powerful,
             intuitive, and scalable solution designed to help businesses build 
             stronger relationships with their customers, streamline operations,
              and drive growth. By centralizing customer data, automating workflows,
               and providing actionable insights, our CRM empowers teams to deliver 
               exceptional customer experiences and achieve their business goals.
           </p>

               <h2>Key Features:</h2>
               <h3>1 Centralized Customer Database </h3>
               <ul> - Store and manage all customer information in one place, 
               including contact details, communication history, purchase behavior, and preferences </ul>
            <ul> -Access a 360-degree view of each customer to personalize interactions and improve engagement.</ul>
            <h3>2 Sales Pipeline Management </h3>  
            <ul>-Track leads, opportunities, and deals through customizable sales pipelines. </ul>
             <ul>-Monitor progress, forecast revenue, and prioritize high-value opportunities.</ul>
             <h3>3 Marketing Automation </h3>
             <u> Create targeted campaigns, automate email marketing, and track campaign performance. </ul>
            <ul>Segment audiences based on behavior, demographics, or preferences for personalized outreach </u>
             <h3>Who Can Benefit?</h3>
            <p>Our CRM is ideal for businesses of all sizes and industries, 
            including sales teams, marketing professionals, customer support agents,
             and business leaders looking to optimize their customer management processes.</p>

       </div>
       </div>

        <!-- Dashboard Section -->
        <div id="dashboard" class="row">
            <div class="col-md-12">
                <h1 class="mb-4">Dashboard</h1>
            </div>
        </div>
        <div class="row">
            <!-- Total Customers -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Total Customers</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo count($_SESSION['customers']); ?></h5>
                    </div>
                </div>
            </div>
            <!-- Pending Tasks -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Pending Tasks</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $pending_count; ?></h5>
                    </div>
                </div>
            </div>
            <!-- Completed Tasks -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Completed Tasks</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $completed_count; ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <div id="customers" class="section">
                <h2>Customers photos</h2>
                
                
                <div class="customer-list">
                    <ul class="customer-grid">
                        
                        <li class="customer-item">
                           
                            <img src="high-angle-people-applauding-work_23-2149636269.avif" alt="Customer 4" class="customer-photo">
                        </li>

                        <li class="customer-item">
                          
                            <img src="people-working-together-medium-shot_52683-99762.avif" alt="Customer 4" class="customer-photo">
                        </li>
                    </ul>
                </div>
            </div>

        <!-- Customer Management Section -->
        <div id="customers" class="row mt-5">
            <div class="col-md-12">
                <h2>Customer Management</h2>
                <div class="card">
                    <div class="card-header">Add New Customer</div>
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="form-group">
                                <input type="text" class="form-control" name="name" placeholder="Name" required>
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control" name="email" placeholder="Email" required>
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" name="phone" placeholder="Phone" required>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" name="address" placeholder="Address" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="profile_pic">Profile Picture</label>
                                <input type="file" class="form-control" name="profile_pic" accept="image/*">
                            </div>
                            <button type="submit" name="add_customer" class="btn btn-primary">Add Customer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="customers" class="section">
                <h1>Customers</h1>
                
                <div class="customer-list">
                  
                    <ul class="customer-grid">
                        
        
                        <li class="customer-item">
                           
                            <img src="remote_work.avif" alt="Customer 3" class="customer-photo">
                        </li>
                        
                        <li class="customer-item">
                            
                            <img src="pexels-photo-2763964.jpeg" alt="Customer 4" class="customer-photo">
                        </li>
                    </ul>
                </div>
            </div>

        <!-- Task Management Section -->
        <div id="tasks" class="row mt-5">
            <div class="col-md-12">
                <h2>Task Management</h2>
                <div class="card">
                    <div class="card-header">Add New Task</div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <input type="text" class="form-control" name="task_title" placeholder="Task Title" required>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" name="task_description" placeholder="Task Description" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <select class="form-control" name="task_status">
                                    <option value="pending">Pending</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <select class="form-control" name="customer_id">
                                    <?php foreach ($_SESSION['customers'] as $id => $customer): ?>
                                        <option value="<?php echo $id; ?>"><?php echo $customer['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="add_task" class="btn btn-primary">Add Task</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer List Section -->
        <div class="row mt-5">
            <div class="col-md-12">
                <h2>Customer List</h2>
                <?php if ($search_term): ?>
                    <p>Showing <?php echo count($filtered_customers); ?> customers matching "<?php echo htmlspecialchars($search_term); ?>"</p>
                <?php endif; ?>
                <p>Sort by: <a href="?sort=name_asc">Name Asc</a> | <a href="?sort=name_desc">Name Desc</a></p>
                <div class="card">
                    <div class="card-header">All Customers</div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Profile</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($filtered_customers)): ?>
                                    <?php foreach ($filtered_customers as $id => $customer): ?>
                                        <tr>
                                            <td><?php echo $id + 1; ?></td>
                                            <td><img src="uploads/<?php echo htmlspecialchars($customer['profile_pic']); ?>" class="profile-pic" alt="Profile Picture"></td>
                                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($customer['address']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" onclick="editCustomer(<?php echo $id; ?>)">Edit</button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteCustomer(<?php echo $id; ?>)">Delete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7">No customers found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
 <!-- Login Modal -->
 <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    
                    <h5 class="modal-title" id="loginModalLabel">Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" name="username" placeholder="Enter username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Enter password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Sign Up Modal -->
    <div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="signupModalLabel">Sign Up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="fullName">Full Name</label>
                            <input type="text" class="form-control" name="fullName" placeholder="Enter full name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="Enter email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Enter password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword">Confirm Password</label>
                            <input type="password" class="form-control" name="confirmPassword" placeholder="Confirm password" required>
                        </div>
                        <button type="submit" name="signup" class="btn btn-primary">Sign Up</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Home</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Follow Us</h5>
                    <ul class="list-unstyled">
                        <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
                        <li><a href="#"><i class="fab fa-twitter"></i> Twitter</a></li>
                        <li><a href="#"><i class="fab fa-instagram"></i> Instagram</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Info</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt"></i> 123 CRM Street, City, Country</li>
                        <li><i class="fas fa-phone"></i> +123 456 7890</li>
                        <li><i class="fas fa-envelope"></i> info@crm.com</li>
                    </ul>
                </div>
            </div>
            <hr>
            <p class="mb-0">© 2023 CRM System. All rights reserved.</p>
        </div>
    </footer>

   
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        function editCustomer(id) {
            alert(`Edit customer with ID: ${id}`);
        }

        function deleteCustomer(id) {
            if (confirm("Are you sure you want to delete this customer?")) {
                alert(`Delete customer with ID: ${id}`);
            }
        }
    </script>
</body>
</html>
        
