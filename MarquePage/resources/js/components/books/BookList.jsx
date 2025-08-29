import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { 
    PlusIcon, 
    MagnifyingGlassIcon,
    FunnelIcon,
    PencilIcon,
    TrashIcon
} from '@heroicons/react/24/outline';
import bookService from '../../services/bookService';
import LoadingSpinner from '../ui/LoadingSpinner';

const BookList = () => {
    const [books, setBooks] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState('');
    const [genreFilter, setGenreFilter] = useState('');

    useEffect(() => {
        loadBooks();
    }, [search, statusFilter, genreFilter]);

    const loadBooks = async () => {
        try {
            const params = {};
            if (search) params.search = search;
            if (statusFilter) params.status = statusFilter;
            if (genreFilter) params.genre = genreFilter;

            const response = await bookService.getBooks(params);
            setBooks(response.data.data.data);
        } catch (error) {
            console.error('Failed to load books:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (bookId) => {
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce livre ?')) {
            return;
        }

        try {
            await bookService.deleteBook(bookId);
            setBooks(books.filter(book => book.id !== bookId));
        } catch (error) {
            console.error('Failed to delete book:', error);
            alert('Erreur lors de la suppression du livre');
        }
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center py-12">
                <LoadingSpinner size="lg" />
            </div>
        );
    }

    return (
        <div className="max-w-7xl mx-auto">
            <div className="sm:flex sm:items-center">
                <div className="sm:flex-auto">
                    <h1 className="text-2xl font-semibold text-gray-900">Ma bibliothèque</h1>
                    <p className="mt-2 text-sm text-gray-700">
                        Gérez votre collection de livres personnelle
                    </p>
                </div>
                <div className="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                    <Link
                        to="/books/new"
                        className="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"
                    >
                        <PlusIcon className="-ml-1 mr-2 h-5 w-5" />
                        Ajouter un livre
                    </Link>
                </div>
            </div>

            {/* Filters */}
            <div className="mt-6 bg-white shadow rounded-lg">
                <div className="px-4 py-5 sm:p-6">
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div>
                            <label htmlFor="search" className="block text-sm font-medium text-gray-700">
                                Rechercher
                            </label>
                            <div className="mt-1 relative rounded-md shadow-sm">
                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <MagnifyingGlassIcon className="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    type="text"
                                    id="search"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Titre, auteur, genre..."
                                />
                            </div>
                        </div>

                        <div>
                            <label htmlFor="status" className="block text-sm font-medium text-gray-700">
                                Statut
                            </label>
                            <select
                                id="status"
                                value={statusFilter}
                                onChange={(e) => setStatusFilter(e.target.value)}
                                className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                            >
                                <option value="">Tous les statuts</option>
                                <option value="to_read">À lire</option>
                                <option value="reading">En cours</option>
                                <option value="completed">Terminé</option>
                            </select>
                        </div>

                        <div>
                            <label htmlFor="genre" className="block text-sm font-medium text-gray-700">
                                Genre
                            </label>
                            <input
                                type="text"
                                id="genre"
                                value={genreFilter}
                                onChange={(e) => setGenreFilter(e.target.value)}
                                className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                placeholder="Genre..."
                            />
                        </div>
                    </div>
                </div>
            </div>

            {/* Books List */}
            <div className="mt-8">
                {books.length > 0 ? (
                    <div className="bg-white shadow overflow-hidden sm:rounded-md">
                        <ul className="divide-y divide-gray-200">
                            {books.map((book) => (
                                <li key={book.id}>
                                    <div className="px-4 py-4 flex items-center justify-between hover:bg-gray-50">
                                        <div className="flex items-center">
                                            <div className="flex-shrink-0 h-16 w-12">
                                                {book.cover_image ? (
                                                    <img
                                                        className="h-16 w-12 rounded object-cover"
                                                        src={`/storage/${book.cover_image}`}
                                                        alt={book.title}
                                                    />
                                                ) : (
                                                    <div className="h-16 w-12 rounded bg-gray-200 flex items-center justify-center">
                                                        <span className="text-gray-400 text-xs">No Image</span>
                                                    </div>
                                                )}
                                            </div>
                                            <div className="ml-4">
                                                <div className="flex items-center">
                                                    <div className="text-sm font-medium text-gray-900">
                                                        <Link 
                                                            to={`/books/${book.id}`}
                                                            className="hover:text-indigo-600"
                                                        >
                                                            {book.title}
                                                        </Link>
                                                    </div>
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                    par {book.author}
                                                </div>
                                                <div className="mt-2 flex items-center space-x-4">
                                                    <span className={`inline-flex px-2 py-1 text-xs rounded-full ${
                                                        book.status === 'completed' 
                                                            ? 'bg-green-100 text-green-800'
                                                            : book.status === 'reading'
                                                            ? 'bg-yellow-100 text-yellow-800'
                                                            : 'bg-gray-100 text-gray-800'
                                                    }`}>
                                                        {book.status === 'to_read' ? 'À lire' : 
                                                         book.status === 'reading' ? 'En cours' : 'Terminé'}
                                                    </span>
                                                    {book.genre && (
                                                        <span className="text-xs text-gray-500">
                                                            {book.genre}
                                                        </span>
                                                    )}
                                                    {book.total_pages && (
                                                        <span className="text-xs text-gray-500">
                                                            {book.current_page}/{book.total_pages} pages ({book.progress_percentage}%)
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Link
                                                to={`/books/${book.id}/edit`}
                                                className="text-indigo-600 hover:text-indigo-900"
                                            >
                                                <PencilIcon className="h-5 w-5" />
                                            </Link>
                                            <button
                                                onClick={() => handleDelete(book.id)}
                                                className="text-red-600 hover:text-red-900"
                                            >
                                                <TrashIcon className="h-5 w-5" />
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </div>
                ) : (
                    <div className="text-center bg-white shadow rounded-lg py-12">
                        <FunnelIcon className="mx-auto h-12 w-12 text-gray-400" />
                        <h3 className="mt-2 text-sm font-medium text-gray-900">Aucun livre trouvé</h3>
                        <p className="mt-1 text-sm text-gray-500">
                            Commencez par ajouter votre premier livre ou ajustez vos filtres.
                        </p>
                        <div className="mt-6">
                            <Link
                                to="/books/new"
                                className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                            >
                                <PlusIcon className="-ml-1 mr-2 h-5 w-5" />
                                Ajouter un livre
                            </Link>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default BookList;