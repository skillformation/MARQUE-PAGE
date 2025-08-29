import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { 
    HomeIcon, 
    BookOpenIcon, 
    PlusIcon,
    UserIcon,
    ArrowRightOnRectangleIcon
} from '@heroicons/react/24/outline';

const Navbar = () => {
    const { user, logout } = useAuth();
    const location = useLocation();

    const handleLogout = () => {
        logout();
    };

    const isActive = (path) => {
        return location.pathname === path;
    };

    return (
        <nav className="bg-white shadow-lg">
            <div className="max-w-7xl mx-auto px-4">
                <div className="flex justify-between">
                    <div className="flex">
                        <div className="flex-shrink-0 flex items-center">
                            <Link to="/" className="text-xl font-bold text-indigo-600">
                                MarquePage
                            </Link>
                        </div>
                        
                        <div className="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <Link
                                to="/"
                                className={`inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium ${
                                    isActive('/') 
                                        ? 'border-indigo-500 text-gray-900' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                            >
                                <HomeIcon className="w-4 h-4 mr-2" />
                                Tableau de bord
                            </Link>
                            
                            <Link
                                to="/books"
                                className={`inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium ${
                                    isActive('/books') || location.pathname.startsWith('/books')
                                        ? 'border-indigo-500 text-gray-900' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                            >
                                <BookOpenIcon className="w-4 h-4 mr-2" />
                                Ma bibliothèque
                            </Link>
                            
                            <Link
                                to="/books/new"
                                className={`inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium ${
                                    isActive('/books/new') 
                                        ? 'border-indigo-500 text-gray-900' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                            >
                                <PlusIcon className="w-4 h-4 mr-2" />
                                Ajouter un livre
                            </Link>
                        </div>
                    </div>
                    
                    <div className="hidden sm:ml-6 sm:flex sm:items-center">
                        <div className="ml-3 relative">
                            <div className="flex items-center space-x-4">
                                <div className="flex items-center text-sm text-gray-700">
                                    <UserIcon className="w-4 h-4 mr-2" />
                                    {user?.name}
                                </div>
                                <button
                                    onClick={handleLogout}
                                    className="inline-flex items-center text-sm text-gray-500 hover:text-gray-700"
                                >
                                    <ArrowRightOnRectangleIcon className="w-4 h-4 mr-1" />
                                    Déconnexion
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    {/* Mobile menu button */}
                    <div className="sm:hidden flex items-center">
                        <div className="flex items-center space-x-2 text-sm text-gray-700">
                            <UserIcon className="w-4 h-4" />
                            <span>{user?.name}</span>
                            <button
                                onClick={handleLogout}
                                className="text-gray-500 hover:text-gray-700"
                            >
                                <ArrowRightOnRectangleIcon className="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            {/* Mobile Navigation */}
            <div className="sm:hidden">
                <div className="pt-2 pb-3 space-y-1">
                    <Link
                        to="/"
                        className={`block pl-3 pr-4 py-2 border-l-4 text-base font-medium ${
                            isActive('/') 
                                ? 'bg-indigo-50 border-indigo-500 text-indigo-700' 
                                : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'
                        }`}
                    >
                        <div className="flex items-center">
                            <HomeIcon className="w-5 h-5 mr-3" />
                            Tableau de bord
                        </div>
                    </Link>
                    
                    <Link
                        to="/books"
                        className={`block pl-3 pr-4 py-2 border-l-4 text-base font-medium ${
                            isActive('/books') || location.pathname.startsWith('/books')
                                ? 'bg-indigo-50 border-indigo-500 text-indigo-700' 
                                : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'
                        }`}
                    >
                        <div className="flex items-center">
                            <BookOpenIcon className="w-5 h-5 mr-3" />
                            Ma bibliothèque
                        </div>
                    </Link>
                    
                    <Link
                        to="/books/new"
                        className={`block pl-3 pr-4 py-2 border-l-4 text-base font-medium ${
                            isActive('/books/new') 
                                ? 'bg-indigo-50 border-indigo-500 text-indigo-700' 
                                : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'
                        }`}
                    >
                        <div className="flex items-center">
                            <PlusIcon className="w-5 h-5 mr-3" />
                            Ajouter un livre
                        </div>
                    </Link>
                </div>
            </div>
        </nav>
    );
};

export default Navbar;