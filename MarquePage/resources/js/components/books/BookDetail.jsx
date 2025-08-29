import React from 'react';
import { Link, useParams } from 'react-router-dom';
import { PencilIcon, ArrowLeftIcon } from '@heroicons/react/24/outline';

const BookDetail = () => {
    const { id } = useParams();

    return (
        <div className="max-w-4xl mx-auto">
            <div className="mb-6">
                <Link 
                    to="/books"
                    className="inline-flex items-center text-sm text-gray-500 hover:text-gray-700"
                >
                    <ArrowLeftIcon className="mr-2 h-4 w-4" />
                    Retour à la bibliothèque
                </Link>
            </div>

            <div className="bg-white shadow rounded-lg">
                <div className="px-6 py-4 border-b border-gray-200">
                    <div className="flex items-center justify-between">
                        <h1 className="text-2xl font-bold text-gray-900">
                            Détails du livre
                        </h1>
                        <Link
                            to={`/books/${id}/edit`}
                            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                        >
                            <PencilIcon className="-ml-1 mr-2 h-4 w-4" />
                            Modifier
                        </Link>
                    </div>
                </div>
                <div className="p-6">
                    <div className="text-center">
                        <p className="text-gray-500">Livre ID: {id}</p>
                        <p className="text-sm text-gray-400 mt-2">
                            Composant en cours de développement...
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default BookDetail;