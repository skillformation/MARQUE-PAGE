<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MarquePage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; }
    </style>
</head>
<body class="antialiased bg-gray-50">
    <div id="app">
        <div class="min-h-screen flex items-center justify-center">
            <div class="max-w-md w-full space-y-8 p-8">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-gray-900 mb-2">
                        MarquePage
                    </h2>
                    <p class="text-gray-600 mb-8">Votre bibliothèque personnelle</p>
                </div>

                <form id="authForm" class="space-y-6">
                    <div id="nameField" class="hidden">
                        <label class="block text-sm font-medium text-gray-700">Nom complet</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Votre nom complet">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="votre@email.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Votre mot de passe">
                    </div>

                    <button 
                        type="submit" 
                        id="submitBtn"
                        class="w-full py-2 px-4 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Se connecter
                    </button>
                </form>

                <div class="text-center">
                    <button 
                        id="toggleMode" 
                        class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                        Créer un nouveau compte
                    </button>
                </div>

                <div id="message" class="hidden p-4 rounded-md"></div>
            </div>
        </div>
    </div>

    <script>
        let isRegisterMode = false;
        const toggleBtn = document.getElementById('toggleMode');
        const nameField = document.getElementById('nameField');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('authForm');
        const messageDiv = document.getElementById('message');

        // Toggle entre connexion et inscription
        toggleBtn.addEventListener('click', () => {
            isRegisterMode = !isRegisterMode;
            if (isRegisterMode) {
                nameField.classList.remove('hidden');
                document.getElementById('name').required = true;
                submitBtn.textContent = 'Créer le compte';
                toggleBtn.textContent = 'Se connecter à un compte existant';
            } else {
                nameField.classList.add('hidden');
                document.getElementById('name').required = false;
                submitBtn.textContent = 'Se connecter';
                toggleBtn.textContent = 'Créer un nouveau compte';
            }
        });

        // Gestion du formulaire
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => data[key] = value);
            
            if (isRegisterMode) {
                data.password_confirmation = data.password;
            }

            const url = isRegisterMode ? '/api/auth/register' : '/api/auth/login';
            
            try {
                submitBtn.textContent = 'Chargement...';
                submitBtn.disabled = true;
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (result.success) {
                    messageDiv.className = 'p-4 rounded-md bg-green-100 text-green-800';
                    messageDiv.textContent = 'Connexion réussie ! Bienvenue ' + result.data.user.name;
                    messageDiv.classList.remove('hidden');
                    
                    // Charger le dashboard complet
                    setTimeout(() => {
                        localStorage.setItem('token', result.data.token);
                        localStorage.setItem('user', JSON.stringify(result.data.user));
                        loadDashboard(result.data.user, result.data.token);
                    }, 1500);
                } else {
                    messageDiv.className = 'p-4 rounded-md bg-red-100 text-red-800';
                    messageDiv.textContent = result.message || 'Erreur de connexion';
                    messageDiv.classList.remove('hidden');
                }
            } catch (error) {
                messageDiv.className = 'p-4 rounded-md bg-red-100 text-red-800';
                messageDiv.textContent = 'Erreur: ' + error.message;
                messageDiv.classList.remove('hidden');
            } finally {
                submitBtn.textContent = isRegisterMode ? 'Créer le compte' : 'Se connecter';
                submitBtn.disabled = false;
            }
        });

        // Fonction pour charger le dashboard complet avec gestion des livres
        function loadDashboard(user, token) {
            document.getElementById('app').innerHTML = `
                <div class="min-h-screen bg-gray-50">
                    <nav class="bg-white shadow">
                        <div class="max-w-7xl mx-auto px-4 py-4">
                            <div class="flex justify-between items-center">
                                <h1 class="text-2xl font-bold text-blue-600 cursor-pointer" onclick="showDashboard()">MarquePage</h1>
                                <div class="flex items-center space-x-6">
                                    <button onclick="showBooks()" class="text-gray-600 hover:text-blue-600">Ma bibliothèque</button>
                                    <button onclick="showAddBook()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                        Ajouter un livre
                                    </button>
                                    <div class="flex items-center space-x-4">
                                        <span class="text-gray-700">Bonjour ${user.name}</span>
                                        <button onclick="logout()" class="bg-red-500 text-white px-3 py-1 rounded text-sm">
                                            Déconnexion
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </nav>
                    
                    <main class="max-w-7xl mx-auto py-8 px-4">
                        <div id="content">
                            <!-- Le contenu sera chargé ici -->
                        </div>
                    </main>
                </div>
            `;
            
            // Charger les statistiques et afficher le dashboard
            loadStats();
            showDashboard();
        }

        // Fonction pour afficher le dashboard principal
        function showDashboard() {
            const content = document.getElementById('content');
            content.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" id="statsCards">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold mb-2">Ma bibliothèque</h3>
                        <p class="text-gray-600" id="totalBooks">Chargement...</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold mb-2">En cours de lecture</h3>
                        <p class="text-gray-600" id="readingBooks">Chargement...</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold mb-2">Terminés</h3>
                        <p class="text-gray-600" id="completedBooks">Chargement...</p>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <h2 class="text-xl font-bold mb-4">Bienvenue dans MarquePage !</h2>
                    <p class="text-gray-600 mb-4">
                        ✅ Authentification fonctionnelle<br/>
                        ✅ Gestion complète des livres<br/>
                        ✅ Upload d'images de couverture<br/>
                        ✅ Suivi de progression de lecture<br/>
                        ✅ Application entièrement opérationnelle
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Livres récents</h2>
                        <button onclick="showBooks()" class="text-blue-600 hover:text-blue-800">Voir tout</button>
                    </div>
                    <div id="recentBooks">Chargement...</div>
                </div>
            `;
            loadStats();
            loadRecentBooks();
        }

        // Fonction pour charger les statistiques
        async function loadStats() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/books', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                
                if (result.success) {
                    const books = result.data.data;
                    const total = books.length;
                    const reading = books.filter(book => book.status === 'reading').length;
                    const completed = books.filter(book => book.status === 'completed').length;
                    
                    if (document.getElementById('totalBooks')) {
                        document.getElementById('totalBooks').textContent = total + ' livre(s)';
                        document.getElementById('readingBooks').textContent = reading + ' livre(s)';
                        document.getElementById('completedBooks').textContent = completed + ' livre(s)';
                    }
                }
            } catch (error) {
                console.error('Erreur lors du chargement des stats:', error);
            }
        }

        // Fonction pour charger les livres récents
        async function loadRecentBooks() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/books?per_page=5', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                
                const container = document.getElementById('recentBooks');
                if (result.success && result.data.data.length > 0) {
                    container.innerHTML = result.data.data.map(book => `
                        <div class="flex items-center space-x-4 p-3 hover:bg-gray-50 rounded">
                            <div class="w-12 h-16 bg-gray-200 rounded flex items-center justify-center text-xs">
                                ${book.cover_image ? 
                                    '<img src="/storage/' + book.cover_image + '" class="w-full h-full object-cover rounded">' : 
                                    'Pas d\'image'
                                }
                            </div>
                            <div class="flex-1">
                                <h4 class="font-medium">${book.title}</h4>
                                <p class="text-sm text-gray-600">par ${book.author}</p>
                                <span class="inline-block px-2 py-1 text-xs rounded ${getStatusColor(book.status)}">
                                    ${getStatusText(book.status)}
                                </span>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<p class="text-gray-500 text-center py-4">Aucun livre pour le moment. <button onclick="showAddBook()" class="text-blue-600 hover:underline">Ajouter votre premier livre</button></p>';
                }
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('recentBooks').innerHTML = '<p class="text-red-500">Erreur lors du chargement</p>';
            }
        }

        // Fonction pour afficher la liste des livres
        async function showBooks() {
            const content = document.getElementById('content');
            content.innerHTML = `
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Ma bibliothèque</h1>
                    <div class="flex items-center space-x-3">
                        <div class="flex bg-gray-200 rounded-lg p-1">
                            <button onclick="toggleView('list')" id="listViewBtn" 
                                    class="px-3 py-1 rounded text-sm transition-colors ${currentView === 'list' ? 'bg-white shadow' : 'text-gray-600'}">
                                📄 Liste
                            </button>
                            <button onclick="toggleView('gallery')" id="galleryViewBtn" 
                                    class="px-3 py-1 rounded text-sm transition-colors ${currentView === 'gallery' ? 'bg-white shadow' : 'text-gray-600'}">
                                🖼️ Galerie
                            </button>
                        </div>
                        <button onclick="showAddBook()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Ajouter un livre
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow mb-6 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <input type="text" id="searchBooks" placeholder="Rechercher par titre ou auteur..." 
                               class="px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500" 
                               onkeyup="debounceSearch()" onchange="searchBooks()">
                        <select id="filterStatus" class="px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500" onchange="searchBooks()">
                            <option value="">Tous les statuts</option>
                            <option value="to_read">À lire</option>
                            <option value="reading">En cours</option>
                            <option value="completed">Terminé</option>
                        </select>
                        <input type="text" id="filterGenre" placeholder="Filtrer par genre..." 
                               class="px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500" 
                               onkeyup="debounceSearch()" onchange="searchBooks()">
                        <select id="sortBooks" class="px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500" onchange="applySorting()">
                            <option value="created_desc">Plus récents</option>
                            <option value="created_asc">Plus anciens</option>
                            <option value="title_asc">Titre A-Z</option>
                            <option value="title_desc">Titre Z-A</option>
                            <option value="author_asc">Auteur A-Z</option>
                            <option value="author_desc">Auteur Z-A</option>
                        </select>
                        <button onclick="clearFilters()" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                            Réinitialiser
                        </button>
                    </div>
                </div>

                <div id="booksList" class="bg-white rounded-lg shadow">
                    <div class="p-6">Chargement...</div>
                </div>
                
                <div id="pagination" class="mt-6 flex justify-center">
                    <!-- Pagination sera ajoutée ici -->
                </div>
            `;
            
            loadBooks();
        }

        // Variables de pagination
        let currentPage = 1;
        let totalPages = 1;
        let paginationData = null;

        // Fonction pour charger et afficher les livres
        async function loadBooks(page = 1) {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(`/api/books?page=${page}&per_page=12`, {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                
                if (result.success) {
                    displayBooks(result.data.data);
                    
                    // Gérer les données de pagination
                    paginationData = result.data;
                    currentPage = result.data.current_page;
                    totalPages = result.data.last_page;
                    displayPagination();
                } else {
                    document.getElementById('booksList').innerHTML = '<div class="p-6 text-red-500">Erreur lors du chargement</div>';
                }
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('booksList').innerHTML = '<div class="p-6 text-red-500">Erreur lors du chargement</div>';
            }
        }

        // Variables globales pour la vue
        let currentView = localStorage.getItem('viewMode') || 'list'; // 'list' ou 'gallery'
        let allBooks = [];

        // Fonction pour afficher les livres selon le mode de vue
        function displayBooks(books) {
            allBooks = books;
            const container = document.getElementById('booksList');
            
            if (books.length === 0) {
                container.innerHTML = `
                    <div class="p-12 text-center">
                        <p class="text-gray-500 mb-4">Aucun livre trouvé</p>
                        <button onclick="showAddBook()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                            Ajouter votre premier livre
                        </button>
                    </div>
                `;
                return;
            }

            if (currentView === 'gallery') {
                displayGalleryView(books, container);
            } else {
                displayListView(books, container);
            }
        }

        // Vue liste (actuelle)
        function displayListView(books, container) {
            container.innerHTML = `
                <div class="divide-y">
                    ${books.map(book => `
                        <div class="p-6 hover:bg-gray-50">
                            <div class="flex items-start space-x-4">
                                <div class="w-20 h-28 bg-gray-200 rounded flex-shrink-0">
                                    ${book.cover_image ? 
                                        '<img src="/storage/' + book.cover_image + '" class="w-full h-full object-cover rounded">' : 
                                        '<div class="w-full h-full flex items-center justify-center text-xs text-gray-500">Pas d\'image</div>'
                                    }
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold">${book.title}</h3>
                                    <p class="text-gray-600">par ${book.author}</p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        ${book.genre || 'Genre non spécifié'} 
                                        ${book.total_pages ? '• ' + book.total_pages + ' pages' : ''}
                                    </p>
                                    ${book.summary ? '<p class="text-sm text-gray-700 mt-2">' + book.summary.substring(0, 150) + (book.summary.length > 150 ? '...' : '') + '</p>' : ''}
                                    
                                    ${book.total_pages ? `
                                        <div class="mt-3">
                                            <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
                                                <span>Progression</span>
                                                <span>${book.current_page || 0}/${book.total_pages} pages (${Math.min(100, Math.round(((book.current_page || 0) / book.total_pages) * 100))}%)</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                                <div class="bg-blue-600 h-2 rounded-full" style="width: ${Math.min(100, Math.round(((book.current_page || 0) / book.total_pages) * 100))}%"></div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <input type="number" id="progress_${book.id}" value="${book.current_page || 0}" min="0" max="${book.total_pages}" 
                                                       class="w-20 px-2 py-1 text-sm border rounded focus:ring-1 focus:ring-blue-500" 
                                                       placeholder="Page">
                                                <button onclick="updateProgress(${book.id})" class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700">
                                                    Mettre à jour
                                                </button>
                                            </div>
                                        </div>
                                    ` : ''}
                                    
                                    <div class="flex items-center justify-between mt-4">
                                        <span class="inline-block px-3 py-1 text-sm rounded ${getStatusColor(book.status)}">
                                            ${getStatusText(book.status)}
                                        </span>
                                        <div class="space-x-2">
                                            <button onclick="editBook(${book.id})" class="text-blue-600 hover:underline text-sm">
                                                Modifier
                                            </button>
                                            <button onclick="deleteBook(${book.id})" class="text-red-600 hover:underline text-sm">
                                                Supprimer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        // Vue galerie
        function displayGalleryView(books, container) {
            container.innerHTML = `
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 p-4">
                    ${books.map(book => `
                        <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200 overflow-hidden">
                            <div class="aspect-[2/3] bg-gray-200 relative">
                                ${book.cover_image ? 
                                    '<img src="/storage/' + book.cover_image + '" class="w-full h-full object-cover">' : 
                                    '<div class="w-full h-full flex items-center justify-center text-xs text-gray-500 text-center p-2">Pas d\'image</div>'
                                }
                                <div class="absolute top-2 right-2">
                                    <span class="inline-block px-2 py-1 text-xs rounded ${getStatusColor(book.status)}">
                                        ${getStatusText(book.status)}
                                    </span>
                                </div>
                                ${book.total_pages ? `
                                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/50 to-transparent p-2">
                                        <div class="w-full bg-white/20 rounded-full h-1">
                                            <div class="bg-white h-1 rounded-full" style="width: ${Math.min(100, Math.round(((book.current_page || 0) / book.total_pages) * 100))}%"></div>
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                            <div class="p-3">
                                <h4 class="font-medium text-sm truncate" title="${book.title}">${book.title}</h4>
                                <p class="text-xs text-gray-600 truncate" title="${book.author}">par ${book.author}</p>
                                ${book.total_pages ? `
                                    <div class="mt-2 flex items-center space-x-1">
                                        <input type="number" id="progress_gallery_${book.id}" value="${book.current_page || 0}" min="0" max="${book.total_pages}" 
                                               class="w-12 px-1 py-0.5 text-xs border rounded" 
                                               title="Page actuelle">
                                        <span class="text-xs text-gray-500">/${book.total_pages}</span>
                                        <button onclick="updateProgress(${book.id}, true)" class="px-2 py-0.5 text-xs bg-green-600 text-white rounded hover:bg-green-700" title="Mettre à jour">
                                            ↑
                                        </button>
                                    </div>
                                ` : ''}
                                <div class="mt-2 flex justify-between">
                                    <button onclick="editBook(${book.id})" class="text-blue-600 hover:underline text-xs" title="Modifier">
                                        ✏️
                                    </button>
                                    <button onclick="deleteBook(${book.id})" class="text-red-600 hover:underline text-xs" title="Supprimer">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        // Fonction pour afficher le formulaire d'ajout de livre
        function showAddBook() {
            const content = document.getElementById('content');
            content.innerHTML = `
                <div class="max-w-2xl mx-auto">
                    <div class="flex items-center mb-6">
                        <button onclick="showBooks()" class="text-blue-600 hover:underline mr-4">← Retour à la bibliothèque</button>
                        <h1 class="text-2xl font-bold">Ajouter un nouveau livre</h1>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <form id="bookForm" onsubmit="saveBook(event)">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Titre *</label>
                                    <input type="text" id="title" required 
                                           class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Auteur *</label>
                                    <input type="text" id="author" required 
                                           class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ISBN-10/13</label>
                                    <input type="text" id="isbn" 
                                           class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Genre</label>
                                    <input type="text" id="genre" placeholder="Roman, Science-fiction, etc."
                                           class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de pages</label>
                                    <input type="number" id="total_pages" min="1"
                                           class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                                    <select id="status" class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="to_read">À lire</option>
                                        <option value="reading">En cours</option>
                                        <option value="completed">Terminé</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Image de couverture</label>
                                <input type="file" id="cover_image" accept="image/*"
                                       class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF - Maximum 5MB</p>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Résumé</label>
                                <textarea id="summary" rows="4" 
                                          class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                          placeholder="Décrivez brièvement le contenu du livre..."></textarea>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="showBooks()" 
                                        class="px-6 py-2 border border-gray-300 rounded hover:bg-gray-50">
                                    Annuler
                                </button>
                                <button type="submit" 
                                        class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Ajouter le livre
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
        }

        // Fonction pour sauvegarder un livre
        async function saveBook(event) {
            event.preventDefault();
            
            const formData = new FormData();
            formData.append('title', document.getElementById('title').value);
            formData.append('author', document.getElementById('author').value);
            formData.append('isbn', document.getElementById('isbn').value);
            formData.append('genre', document.getElementById('genre').value);
            formData.append('total_pages', document.getElementById('total_pages').value);
            formData.append('status', document.getElementById('status').value);
            formData.append('summary', document.getElementById('summary').value);
            
            const coverImage = document.getElementById('cover_image').files[0];
            if (coverImage) {
                formData.append('cover_image', coverImage);
            }

            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/books', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token
                    },
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    alert('Livre ajouté avec succès !');
                    showBooks();
                } else {
                    alert('Erreur: ' + (result.message || 'Erreur inconnue'));
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'ajout du livre');
            }
        }

        // Fonction pour supprimer un livre
        async function deleteBook(bookId) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce livre ?')) {
                return;
            }

            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/books/' + bookId, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + token
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    alert('Livre supprimé avec succès !');
                    loadBooks();
                    loadStats();
                } else {
                    alert('Erreur: ' + (result.message || 'Erreur inconnue'));
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de la suppression');
            }
        }

        // Fonctions utilitaires
        function getStatusColor(status) {
            switch(status) {
                case 'completed': return 'bg-green-100 text-green-800';
                case 'reading': return 'bg-yellow-100 text-yellow-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }

        function getStatusText(status) {
            switch(status) {
                case 'completed': return 'Terminé';
                case 'reading': return 'En cours';
                default: return 'À lire';
            }
        }

        function logout() {
            localStorage.clear();
            location.reload();
        }

        // Fonction de recherche avancée avec pagination
        async function searchBooks(page = 1) {
            const search = document.getElementById('searchBooks')?.value || '';
            const status = document.getElementById('filterStatus')?.value || '';
            const genre = document.getElementById('filterGenre')?.value || '';
            
            try {
                const token = localStorage.getItem('token');
                const params = new URLSearchParams();
                
                params.append('page', page);
                params.append('per_page', 12);
                
                if (search.trim()) params.append('search', search.trim());
                if (status) params.append('status', status);
                if (genre.trim()) params.append('genre', genre.trim());
                
                const url = `/api/books?${params.toString()}`;
                const response = await fetch(url, {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    displayBooks(result.data.data);
                    
                    // Gérer les données de pagination pour la recherche
                    paginationData = result.data;
                    currentPage = result.data.current_page;
                    totalPages = result.data.last_page;
                    displaySearchPagination();
                } else {
                    displayBooks([]);
                }
                
            } catch (error) {
                console.error('Erreur lors de la recherche:', error);
                document.getElementById('booksList').innerHTML = '<div class="p-6 text-red-500">Erreur lors de la recherche</div>';
            }
        }

        // Fonction de pagination spécifique pour la recherche
        function displaySearchPagination() {
            const paginationContainer = document.getElementById('pagination');
            if (!paginationContainer || totalPages <= 1) {
                if (paginationContainer) paginationContainer.innerHTML = '';
                return;
            }

            let paginationHTML = '<div class="flex items-center space-x-2">';
            
            // Bouton Précédent
            if (currentPage > 1) {
                paginationHTML += `
                    <button onclick="searchBooks(${currentPage - 1})" 
                            class="px-3 py-2 border rounded hover:bg-gray-100">
                        ← Précédent
                    </button>
                `;
            }
            
            // Pages (simplifiée pour la recherche)
            for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
                paginationHTML += `
                    <button onclick="searchBooks(${i})" 
                            class="px-3 py-2 border rounded ${i === currentPage ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'}">
                        ${i}
                    </button>
                `;
            }
            
            // Bouton Suivant
            if (currentPage < totalPages) {
                paginationHTML += `
                    <button onclick="searchBooks(${currentPage + 1})" 
                            class="px-3 py-2 border rounded hover:bg-gray-100">
                        Suivant →
                    </button>
                `;
            }
            
            paginationHTML += '</div>';
            
            // Informations de pagination
            if (paginationData) {
                paginationHTML += `
                    <div class="text-sm text-gray-600 mt-2 text-center">
                        ${paginationData.total} résultat(s) trouvé(s)
                    </div>
                `;
            }
            
            paginationContainer.innerHTML = paginationHTML;
        }

        async function editBook(bookId) {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(`/api/books/${bookId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                if (response.ok) {
                    const result = await response.json();
                    const book = result.data;
                    showEditBookForm(book);
                } else {
                    throw new Error('Erreur lors de la récupération du livre');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de la récupération du livre');
            }
        }

        function showEditBookForm(book) {
            const content = document.getElementById('content');
            content.innerHTML = `
                <div class="max-w-2xl mx-auto">
                    <div class="flex items-center mb-6">
                        <button onclick="showBooks()" class="text-blue-600 hover:underline mr-4">← Retour à la bibliothèque</button>
                        <h1 class="text-2xl font-bold">Modifier le livre</h1>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <form id="editBookForm" onsubmit="updateBook(event, ${book.id})">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Titre *</label>
                                    <input type="text" id="edit_title" value="${book.title || ''}" required 
                                           class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Auteur *</label>
                                    <input type="text" id="edit_author" value="${book.author || ''}" required 
                                           class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ISBN-10/13</label>
                                    <input type="text" id="edit_isbn" value="${book.isbn || ''}"
                                           class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Genre</label>
                                    <input type="text" id="edit_genre" value="${book.genre || ''}" placeholder="Roman, Science-fiction, etc."
                                           class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de pages</label>
                                    <input type="number" id="edit_total_pages" value="${book.total_pages || ''}" min="1"
                                           class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Page actuelle</label>
                                    <input type="number" id="edit_current_page" value="${book.current_page || 0}" min="0"
                                           class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                                    <select id="edit_status" class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="to_read" ${book.status === 'to_read' ? 'selected' : ''}>À lire</option>
                                        <option value="reading" ${book.status === 'reading' ? 'selected' : ''}>En cours</option>
                                        <option value="completed" ${book.status === 'completed' ? 'selected' : ''}>Terminé</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Note (0-5)</label>
                                    <input type="number" id="edit_rating" value="${book.rating || ''}" min="0" max="5" step="0.1"
                                           class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nouvelle image de couverture</label>
                                <input type="file" id="edit_cover_image" accept="image/*"
                                       class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF - Maximum 5MB (laisser vide pour conserver l'image actuelle)</p>
                                ${book.cover_image ? `
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600">Image actuelle:</p>
                                        <img src="/storage/${book.cover_image}" alt="Couverture actuelle" class="w-20 h-28 object-cover rounded border mt-1">
                                    </div>
                                ` : ''}
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Résumé</label>
                                <textarea id="edit_summary" rows="4" 
                                          class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                          placeholder="Décrivez brièvement le contenu du livre...">${book.summary || ''}</textarea>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="showBooks()" 
                                        class="px-6 py-2 border border-gray-300 rounded hover:bg-gray-50">
                                    Annuler
                                </button>
                                <button type="submit" 
                                        class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Mettre à jour
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
        }

        async function updateBook(event, bookId) {
            event.preventDefault();
            
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('title', document.getElementById('edit_title').value);
            formData.append('author', document.getElementById('edit_author').value);
            formData.append('isbn', document.getElementById('edit_isbn').value);
            formData.append('genre', document.getElementById('edit_genre').value);
            formData.append('total_pages', document.getElementById('edit_total_pages').value);
            formData.append('current_page', document.getElementById('edit_current_page').value);
            formData.append('status', document.getElementById('edit_status').value);
            formData.append('rating', document.getElementById('edit_rating').value);
            formData.append('summary', document.getElementById('edit_summary').value);
            
            const coverImageFile = document.getElementById('edit_cover_image').files[0];
            if (coverImageFile) {
                formData.append('cover_image', coverImageFile);
            }
            
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(`/api/books/${bookId}`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    alert('Livre mis à jour avec succès');
                    showBooks();
                } else {
                    throw new Error(result.message || 'Erreur lors de la mise à jour');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de la mise à jour: ' + error.message);
            }
        }

        // Fonction pour afficher la pagination
        function displayPagination() {
            const paginationContainer = document.getElementById('pagination');
            if (!paginationContainer || totalPages <= 1) {
                if (paginationContainer) paginationContainer.innerHTML = '';
                return;
            }

            let paginationHTML = '<div class="flex items-center space-x-2">';
            
            // Bouton Précédent
            if (currentPage > 1) {
                paginationHTML += `
                    <button onclick="loadBooks(${currentPage - 1})" 
                            class="px-3 py-2 border rounded hover:bg-gray-100">
                        ← Précédent
                    </button>
                `;
            }
            
            // Pages
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);
            
            if (startPage > 1) {
                paginationHTML += `
                    <button onclick="loadBooks(1)" 
                            class="px-3 py-2 border rounded hover:bg-gray-100">1</button>
                `;
                if (startPage > 2) {
                    paginationHTML += '<span class="px-2">...</span>';
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                paginationHTML += `
                    <button onclick="loadBooks(${i})" 
                            class="px-3 py-2 border rounded ${i === currentPage ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'}">
                        ${i}
                    </button>
                `;
            }
            
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    paginationHTML += '<span class="px-2">...</span>';
                }
                paginationHTML += `
                    <button onclick="loadBooks(${totalPages})" 
                            class="px-3 py-2 border rounded hover:bg-gray-100">${totalPages}</button>
                `;
            }
            
            // Bouton Suivant
            if (currentPage < totalPages) {
                paginationHTML += `
                    <button onclick="loadBooks(${currentPage + 1})" 
                            class="px-3 py-2 border rounded hover:bg-gray-100">
                        Suivant →
                    </button>
                `;
            }
            
            paginationHTML += '</div>';
            
            // Ajouter les informations de pagination
            if (paginationData) {
                paginationHTML += `
                    <div class="text-sm text-gray-600 mt-2 text-center">
                        Affichage de ${((currentPage - 1) * 12) + 1} à ${Math.min(currentPage * 12, paginationData.total)} 
                        sur ${paginationData.total} livre(s)
                    </div>
                `;
            }
            
            paginationContainer.innerHTML = paginationHTML;
        }

        // Fonctions pour la gestion des vues et filtres
        function toggleView(viewType) {
            currentView = viewType;
            localStorage.setItem('viewMode', viewType);
            
            // Mettre à jour l'apparence des boutons
            document.getElementById('listViewBtn').className = 
                `px-3 py-1 rounded text-sm transition-colors ${viewType === 'list' ? 'bg-white shadow' : 'text-gray-600'}`;
            document.getElementById('galleryViewBtn').className = 
                `px-3 py-1 rounded text-sm transition-colors ${viewType === 'gallery' ? 'bg-white shadow' : 'text-gray-600'}`;
            
            // Réafficher les livres avec la nouvelle vue
            displayBooks(allBooks);
        }

        // Debounce pour la recherche en temps réel
        let searchTimeout;
        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(searchBooks, 300);
        }

        // Fonction pour réinitialiser les filtres
        function clearFilters() {
            document.getElementById('searchBooks').value = '';
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterGenre').value = '';
            document.getElementById('sortBooks').value = 'created_desc';
            loadBooks();
        }

        // Fonction de tri local
        function applySorting() {
            const sortValue = document.getElementById('sortBooks').value;
            let sortedBooks = [...allBooks];
            
            switch(sortValue) {
                case 'title_asc':
                    sortedBooks.sort((a, b) => a.title.localeCompare(b.title));
                    break;
                case 'title_desc':
                    sortedBooks.sort((a, b) => b.title.localeCompare(a.title));
                    break;
                case 'author_asc':
                    sortedBooks.sort((a, b) => a.author.localeCompare(b.author));
                    break;
                case 'author_desc':
                    sortedBooks.sort((a, b) => b.author.localeCompare(a.author));
                    break;
                case 'created_asc':
                    sortedBooks.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                    break;
                default: // created_desc
                    sortedBooks.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            }
            
            displayBooks(sortedBooks);
        }

        async function updateProgress(bookId, isGallery = false) {
            const inputId = isGallery ? `progress_gallery_${bookId}` : `progress_${bookId}`;
            const currentPage = document.getElementById(inputId).value;
            
            if (!currentPage || currentPage < 0) {
                alert('Veuillez entrer un numéro de page valide');
                return;
            }
            
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(`/api/books/${bookId}/progress`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({
                        current_page: parseInt(currentPage)
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Recharger la liste des livres pour voir la progression mise à jour
                    loadBooks();
                    
                    // Afficher un message de succès discret
                    const button = document.querySelector(`button[onclick="updateProgress(${bookId})"]`);
                    const originalText = button.textContent;
                    button.textContent = '✓ Mis à jour';
                    button.className = 'px-3 py-1 text-sm bg-green-500 text-white rounded';
                    
                    setTimeout(() => {
                        button.textContent = originalText;
                        button.className = 'px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700';
                    }, 2000);
                } else {
                    throw new Error(result.message || 'Erreur lors de la mise à jour');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de la mise à jour de la progression: ' + error.message);
            }
        }

        // Auto-connexion si token présent
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('token');
            const user = localStorage.getItem('user');
            
            if (token && user) {
                try {
                    const userData = JSON.parse(user);
                    loadDashboard(userData, token);
                } catch (e) {
                    localStorage.clear();
                }
            }
        });
    </script>
</body>
</html>