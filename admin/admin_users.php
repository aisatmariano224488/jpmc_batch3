<?php
// Start the session
// session_start();


// // Check if user is logged in
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header("Location: admin_login.php");
//     exit;
// }

require_once '../includes/db_connection.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Users - Admin Panel</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0f4c81',
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">

<?php include 'includes/adminsidebar.php'; ?>

<!-- Main Content -->
<div class="lg:ml-64 p-6 min-h-screen transition-all duration-300">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manage Admin Users</h1>
        <p class="text-sm text-gray-600">Create, edit, and manage administrator accounts</p>
    </div>
    
    <!-- Action Buttons -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <button id="addUserButton" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-blue-800 transition-colors flex items-center">
                <i class="fas fa-plus mr-2"></i> Add New Admin User
            </button>
        </div>
        <div class="relative">
            <input type="text" id="searchUsers" class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Search users...">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        </div>
    </div>
    
    <!-- Users Table -->
    <div class="bg-white rounded-md shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    // Fetch admin users from database
                    $query = "SELECT * FROM admin_users ORDER BY created_at DESC";
                    $result = $conn->query($query);

                    if ($result && $result->num_rows > 0) {
                        while ($user = $result->fetch_assoc()) {
                            $statusClass = ($user['is_active'] == 1) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                            $statusText = ($user['is_active'] == 1) ? 'Active' : 'Inactive';
                            
                            echo '<tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-primary text-white rounded-full flex items-center justify-center">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">' . htmlspecialchars($user['name']) . '</div>
                                            <div class="text-sm text-gray-500">' . htmlspecialchars($user['email']) . '</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Administrator</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $statusClass . '">
                                        ' . $statusText . '
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    ' . ($user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never') . '
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="#" class="text-primary hover:text-blue-800 mr-3 edit-user" data-id="' . $user['id'] . '"><i class="fas fa-edit"></i></a>
                                    <a href="#" class="text-red-600 hover:text-red-800 delete-user" data-id="' . $user['id'] . '"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5" class="px-6 py-4 text-center">No users found</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="flex justify-between items-center mt-6">
        <div class="text-sm text-gray-700">
            Showing <span class="font-medium">1</span> to <span class="font-medium">10</span> of <span class="font-medium">20</span> results
        </div>
        <div class="flex">
            <a href="#" class="px-3 py-1 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-l-md">Previous</a>
            <a href="#" class="px-3 py-1 border border-gray-300 bg-white text-sm font-medium text-primary hover:bg-gray-50">1</a>
            <a href="#" class="px-3 py-1 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">2</a>
            <a href="#" class="px-3 py-1 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-r-md">Next</a>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="flex justify-between items-center border-b p-4">
            <h3 class="text-lg font-bold text-gray-900" id="modalTitle">Add New Admin User</h3>
            <button id="closeModal" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="userForm">
            <div class="p-4">
                <input type="hidden" id="userId" name="userId" value="">
                
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" id="name" name="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                </div>
                
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" id="email" name="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="password" name="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to keep current password (when editing)</p>
                </div>
                
                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 flex justify-end space-x-3 rounded-b-lg">
                <button type="button" id="cancelButton" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Cancel
                </button>
                <button type="submit" class="bg-primary py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Save User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Confirm Deletion</h3>
            <p class="text-gray-700">Are you sure you want to delete this user? This action cannot be undone.</p>
            <input type="hidden" id="deleteUserId" value="">
            
            <div class="mt-6 flex justify-end space-x-3">
                <button id="cancelDelete" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Cancel
                </button>
                <button id="confirmDelete" class="bg-red-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Delete User
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const userModal = document.getElementById('userModal');
    const deleteModal = document.getElementById('deleteModal');
    const modalTitle = document.getElementById('modalTitle');
    const userForm = document.getElementById('userForm');
    const addUserButton = document.getElementById('addUserButton');
    const closeModal = document.getElementById('closeModal');
    const cancelButton = document.getElementById('cancelButton');
    const searchInput = document.getElementById('searchUsers');
    
    // Delete modal elements
    const cancelDelete = document.getElementById('cancelDelete');
    const confirmDelete = document.getElementById('confirmDelete');
    
    // Show add user modal
    addUserButton.addEventListener('click', function() {
        modalTitle.textContent = 'Add New Admin User';
        userForm.reset();
        document.getElementById('userId').value = '';
        document.getElementById('password').required = true;
        toggleModal(userModal, true);
    });
    
    // Close modals
    closeModal.addEventListener('click', () => toggleModal(userModal, false));
    cancelButton.addEventListener('click', () => toggleModal(userModal, false));
    cancelDelete.addEventListener('click', () => toggleModal(deleteModal, false));
    
    // Handle click outside modals
    window.addEventListener('click', function(e) {
        if (e.target === userModal) toggleModal(userModal, false);
        if (e.target === deleteModal) toggleModal(deleteModal, false);
    });
      // Edit user
    document.querySelectorAll('.edit-user').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-id');
            
            // Fetch user data from the server
            const formData = new FormData();
            formData.append('action', 'get');
            formData.append('userId', userId);
            
            fetch('admin_user_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.user) {
                    const user = data.user;
                    
                    modalTitle.textContent = 'Edit Admin User';
                    document.getElementById('userId').value = user.id;
                    document.getElementById('password').required = false;
                    
                    document.getElementById('name').value = user.name;
                    document.getElementById('email').value = user.email;
                    document.getElementById('status').value = user.is_active;
                                  } else {
                    alert(data.message || 'Failed to get user details');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while getting user details');
            });
        });
    });
    
    // Delete user
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-id');
            document.getElementById('deleteUserId').value = userId;
            toggleModal(deleteModal, true);
        });
    });
      // Handle delete confirmation
    confirmDelete.addEventListener('click', function() {
        const userId = document.getElementById('deleteUserId').value;
        
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('userId', userId);
        
        // Send AJAX request to delete the user
        fetch('admin_user_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            toggleModal(deleteModal, false);
            
            if (data.success) {
                alert(data.message);
                // Reload the page to show updated data
                window.location.reload();
            } else {
                alert(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request');
        });
    });
      // Handle form submission
    userForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const userId = document.getElementById('userId').value;
        
        // Add appropriate action
        formData.append('action', userId ? 'edit' : 'add');
        
        // Send AJAX request
        fetch('admin_user_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toggleModal(userModal, false);
                alert(data.message);
                // Reload the page to show updated data
                window.location.reload();
            } else {
                alert(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request');
        });
    });
    
    // Search functionality
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const name = row.querySelector('td:first-child .text-sm.font-medium');
            const email = row.querySelector('td:first-child .text-sm.text-gray-500');
            
            if (!name || !email) return;
            
            const text = name.textContent.toLowerCase() + email.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Toggle modal visibility
    function toggleModal(modal, show) {
        if (show) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        } else {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }
    }
});
</script>

</body>
</html>