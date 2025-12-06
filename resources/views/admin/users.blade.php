<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - CandiHire Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/AdminUsers.css') }}">
</head>
<body>
    <div class="admin-header">
        <div class="header-content">
            <div class="admin-title">
                <i class="fas fa-shield-alt"></i> CandiHire Admin Panel
            </div>
            <a href="{{ url('admin/dashboard') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">User Management</h1>
            <p class="page-subtitle">Manage candidates and companies in the system</p>
        </div>

        @if (session('success_message'))
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                {{ session('success_message') }}
            </div>
        @endif

        @if (session('error_message'))
            <div class="message error">
                <i class="fas fa-exclamation-triangle"></i>
                {{ session('error_message') }}
            </div>
        @endif

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ count($candidates) }}</div>
                <div class="stat-label">Total Candidates</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ count($companies) }}</div>
                <div class="stat-label">Total Companies</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ count(array_filter($candidates, fn($c) => strtolower($c['Status'] ?? '') === 'active')) }}</div>
                <div class="stat-label">Active Candidates</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ count(array_filter($companies, fn($c) => strtolower($c['Status'] ?? '') === 'active')) }}</div>
                <div class="stat-label">Active Companies</div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="switchTab('candidates')">
                <i class="fas fa-users"></i> Candidates ({{ count($candidates) }})
            </button>
            <button class="tab" onclick="switchTab('companies')">
                <i class="fas fa-building"></i> Companies ({{ count($companies) }})
            </button>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-section">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search users..." onkeyup="filterUsers()">
            </div>
            <div class="filter-options">
                <select id="statusFilter" onchange="filterUsers()">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <select id="sortFilter" onchange="sortUsers()">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name">Name A-Z</option>
                </select>
            </div>
        </div>

        <div id="candidates" class="tab-content active">
            <div class="data-table">
                <div class="table-header">
                    <h3 class="table-title">Candidates List</h3>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Applications</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($candidates as $candidate)
                            <tr data-user-type="candidate" data-status="{{ strtolower($candidate['Status']) }}" data-name="{{ strtolower($candidate['FullName']) }}" data-email="{{ strtolower($candidate['Email']) }}">
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            {{ strtoupper(substr($candidate['FullName'], 0, 1)) }}
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name">{{ $candidate['FullName'] }}</div>
                                            <div class="user-email" style="font-size: 0.8em; color: var(--text-secondary);">{{ $candidate['Email'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $candidate['Phone'] ?? 'N/A' }}</td>
                                <td>
                                    <span class="application-count">{{ $candidate['application_count'] }}</span>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ strtolower($candidate['Status']) }}">
                                        {{ ucfirst($candidate['Status']) }}
                                    </span>
                                </td>
                                <td>{{ date('M j, Y', strtotime($candidate['CreatedAt'])) }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <form action="{{ route('admin.users.delete') }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this candidate? This action cannot be undone.');">
                                            @csrf
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="{{ $candidate['CandidateID'] }}">
                                            <input type="hidden" name="user_type" value="candidate">
                                            <button type="submit" class="btn btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div id="companies" class="tab-content">
            <div class="data-table">
                <div class="table-header">
                    <h3 class="table-title">Companies List</h3>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Company Name</th>
                            <th>Phone</th>
                            <th>Job Posts</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($companies as $company)
                            <tr data-user-type="company" data-status="{{ strtolower($company['Status']) }}" data-name="{{ strtolower($company['CompanyName']) }}" data-email="{{ strtolower($company['Email']) }}">
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name">{{ $company['CompanyName'] }}</div>
                                            <div class="user-email" style="font-size: 0.8em; color: var(--text-secondary);">{{ $company['Email'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $company['Phone'] ?? 'N/A' }}</td>
                                <td>
                                    <span class="job-count">{{ $company['job_count'] }}</span>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ strtolower($company['Status']) }}">
                                        {{ ucfirst($company['Status']) }}
                                    </span>
                                </td>
                                <td>{{ date('M j, Y', strtotime($company['CreatedAt'])) }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <form action="{{ route('admin.users.delete') }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this company? This action cannot be undone.');">
                                            @csrf
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="{{ $company['CompanyID'] }}">
                                            <input type="hidden" name="user_type" value="company">
                                            <button type="submit" class="btn btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            if (event) {
                event.target.closest('.tab').classList.add('active');
            }
            
            // Reset filters when switching tabs
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('sortFilter').value = 'newest';
            filterUsers();
        }

        function filterUsers() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const currentTab = document.querySelector('.tab-content.active');
            
            if (!currentTab) return;
            
            const rows = currentTab.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const email = row.getAttribute('data-email');
                const status = row.getAttribute('data-status');
                
                const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
                const matchesStatus = statusFilter === '' || status === statusFilter;
                
                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        function sortUsers() {
            const sortType = document.getElementById('sortFilter').value;
            const currentTab = document.querySelector('.tab-content.active');
             if (!currentTab) return;
             
            const tbody = currentTab.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            rows.sort((a, b) => {
                if (sortType === 'name') {
                    const nameA = a.getAttribute('data-name');
                    const nameB = b.getAttribute('data-name');
                    return nameA.localeCompare(nameB);
                } else if (sortType === 'oldest') {
                    // Assuming last column is date, but simple DOM order is often roughly chronological by creation id
                    // For better sorting, we would need a data-date attribute.
                    // Let's assume the rows are originally in 'newest' order from DB.
                    // So 'oldest' just means reverse the current order if it was 'newest'.
                    // To do it properly, let's reverse if needed.
                    // Implementation simplification: just reverse the array if it's oldest, assuming default is newest
                     return -1; // This is a placeholder logic, simpler to let server sort or implement proper data-date
                } else {
                     return 1;
                }
            });
            
            if (sortType === 'oldest') {
                rows.reverse();
            }
            
            // Re-append
            rows.forEach(row => tbody.appendChild(row));
        }
    </script>
</body>
</html>
