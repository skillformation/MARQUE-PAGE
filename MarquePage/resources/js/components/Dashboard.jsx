import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { PlusIcon, BookOpenIcon, ClockIcon, CheckIcon } from '@heroicons/react/24/outline';
import bookService from '../services/bookService';
import LoadingSpinner from './ui/LoadingSpinner';

const Dashboard = () => {
    const [stats, setStats] = useState({
        total: 0,
        reading: 0,
        completed: 0,
        toRead: 0
    });
    const [recentBooks, setRecentBooks] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadDashboardData();
    }, []);

    const loadDashboardData = async () => {
        try {
            const response = await bookService.getBooks({ per_page: 10 });
            const books = response.data.data.data;
            
            setRecentBooks(books.slice(0, 5));
            
            const statsData = books.reduce((acc, book) => {
                acc.total++;
                acc[book.status === 'to_read' ? 'toRead' : book.status]++;
                return acc;
            }, { total: 0, reading: 0, completed: 0, toRead: 0 });
            
            setStats(statsData);
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
        } finally {
            setLoading(false);
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
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-gray-900">Tableau de bord</h1>
                <p className="mt-2 text-gray-600">Bienvenue dans votre bibliothèque personnelle</p>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div className="bg-white rounded-lg shadow p-6">
                    <div className="flex items-center">
                        <div className="flex-shrink-0">
                            <BookOpenIcon className="h-8 w-8 text-blue-600" />
                        </div>
                        <div className="ml-4">
                            <p className="text-sm font-medium text-gray-500">Total</p>
                            <p className="text-2xl font-semibold text-gray-900">{stats.total}</p>
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
                    <div className="flex items-center">
                        <div className="flex-shrink-0">
                            <ClockIcon className="h-8 w-8 text-yellow-600" />
                        </div>
                        <div className="ml-4">
                            <p className="text-sm font-medium text-gray-500">En cours</p>
                            <p className="text-2xl font-semibold text-gray-900">{stats.reading}</p>
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
                    <div className="flex items-center">
                        <div className="flex-shrink-0">
                            <CheckIcon className="h-8 w-8 text-green-600" />
                        </div>
                        <div className="ml-4">
                            <p className="text-sm font-medium text-gray-500">Terminés</p>
                            <p className="text-2xl font-semibold text-gray-900">{stats.completed}</p>
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
                    <div className="flex items-center">
                        <div className="flex-shrink-0">
                            <PlusIcon className="h-8 w-8 text-gray-600" />
                        </div>
                        <div className="ml-4">
                            <p className="text-sm font-medium text-gray-500">À lire</p>
                            <p className="text-2xl font-semibold text-gray-900">{stats.toRead}</p>
                        </div>
                    </div>
                </div>
            </div>

            {/* Recent Books */}
            <div className="bg-white rounded-lg shadow">
                <div className="px-6 py-4 border-b border-gray-200">
                    <div className="flex items-center justify-between">
                        <h2 className="text-lg font-medium text-gray-900">Livres récents</h2>
                        <Link
                            to="/books"
                            className="text-indigo-600 hover:text-indigo-500 text-sm font-medium"
                        >
                            Voir tout
                        </Link>
                    </div>
                </div>
                <div className="p-6">
                    {recentBooks.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {recentBooks.map((book) => (
                                <Link
                                    key={book.id}
                                    to={`/books/${book.id}`}
                                    className="block hover:bg-gray-50 rounded-lg p-4 transition-colors"
                                >
                                    <div className="flex items-start space-x-4">
                                        <div className="flex-shrink-0 w-12 h-16 bg-gray-200 rounded"></div>
                                        <div className="flex-1 min-w-0">
                                            <p className="text-sm font-medium text-gray-900 truncate">
                                                {book.title}
                                            </p>
                                            <p className="text-sm text-gray-500 truncate">
                                                {book.author}
                                            </p>
                                            <div className="mt-2">
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
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    ) : (
                        <div className="text-center py-8">
                            <BookOpenIcon className="mx-auto h-12 w-12 text-gray-400" />
                            <h3 className="mt-2 text-sm font-medium text-gray-900">Aucun livre</h3>
                            <p className="mt-1 text-sm text-gray-500">
                                Commencez par ajouter votre premier livre.
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

            {/* Quick Actions */}
            <div className="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <Link
                    to="/books/new"
                    className="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow"
                >
                    <div className="flex items-center">
                        <PlusIcon className="h-8 w-8 text-indigo-600" />
                        <div className="ml-4">
                            <h3 className="text-lg font-medium text-gray-900">Ajouter un livre</h3>
                            <p className="text-sm text-gray-500">Ajoutez un nouveau livre à votre collection</p>
                        </div>
                    </div>
                </Link>

                <Link
                    to="/books?status=reading"
                    className="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow"
                >
                    <div className="flex items-center">
                        <ClockIcon className="h-8 w-8 text-yellow-600" />
                        <div className="ml-4">
                            <h3 className="text-lg font-medium text-gray-900">Continuer la lecture</h3>
                            <p className="text-sm text-gray-500">Reprendre vos lectures en cours</p>
                        </div>
                    </div>
                </Link>

                <Link
                    to="/books"
                    className="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow"
                >
                    <div className="flex items-center">
                        <BookOpenIcon className="h-8 w-8 text-blue-600" />
                        <div className="ml-4">
                            <h3 className="text-lg font-medium text-gray-900">Ma bibliothèque</h3>
                            <p className="text-sm text-gray-500">Parcourir tous mes livres</p>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    );
};

export default Dashboard;