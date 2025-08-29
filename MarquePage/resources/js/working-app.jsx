import './bootstrap';
import React, { useState } from 'react';
import ReactDOM from 'react-dom/client';

// Page de connexion simple
function LoginPage() {
    const [formData, setFormData] = useState({
        email: '',
        password: ''
    });
    const [isRegister, setIsRegister] = useState(false);
    const [user, setUser] = useState(null);

    const handleChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        try {
            const url = isRegister ? '/api/auth/register' : '/api/auth/login';
            const body = isRegister 
                ? { ...formData, name: formData.name, password_confirmation: formData.password }
                : formData;

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(body)
            });

            const data = await response.json();
            
            if (data.success) {
                localStorage.setItem('token', data.data.token);
                setUser(data.data.user);
            } else {
                alert('Erreur: ' + (data.message || 'Connexion échouée'));
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('Erreur de connexion');
        }
    };

    // Page connectée
    if (user) {
        return (
            <div className="min-h-screen bg-gray-50 p-8">
                <div className="max-w-4xl mx-auto">
                    <div className="bg-white rounded-lg shadow p-6 mb-6">
                        <div className="flex justify-between items-center">
                            <h1 className="text-3xl font-bold text-blue-600">
                                Bienvenue {user.name} !
                            </h1>
                            <button
                                onClick={() => {
                                    localStorage.removeItem('token');
                                    setUser(null);
                                }}
                                className="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                            >
                                Déconnexion
                            </button>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div className="bg-white rounded-lg shadow p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-2">Ma bibliothèque</h3>
                            <p className="text-gray-600">Gérez vos livres</p>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-2">Ajouter un livre</h3>
                            <p className="text-gray-600">Nouveau livre</p>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-2">Statistiques</h3>
                            <p className="text-gray-600">Vos lectures</p>
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow p-6">
                        <h2 className="text-xl font-bold mb-4">MarquePage - Votre bibliothèque personnelle</h2>
                        <p className="text-gray-600 mb-4">
                            ✅ Authentification fonctionnelle<br/>
                            ✅ Interface utilisateur responsive<br/>
                            ✅ Backend Laravel 12 avec API RESTful<br/>
                            ✅ Base de données MySQL configurée<br/>
                            ✅ Système de gestion des livres prêt
                        </p>
                        <div className="text-sm text-gray-500">
                            Application développée avec Laravel 12 + React + Tailwind CSS
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    // Page de connexion/inscription
    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50">
            <div className="max-w-md w-full space-y-8">
                <div>
                    <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        {isRegister ? 'Créer votre compte' : 'Connexion à MarquePage'}
                    </h2>
                    <p className="mt-2 text-center text-sm text-gray-600">
                        {isRegister ? 'Ou ' : 'Ou '}
                        <button
                            type="button"
                            onClick={() => setIsRegister(!isRegister)}
                            className="font-medium text-indigo-600 hover:text-indigo-500"
                        >
                            {isRegister ? 'connectez-vous à votre compte' : 'créez un nouveau compte'}
                        </button>
                    </p>
                </div>

                <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
                    <div className="space-y-4">
                        {isRegister && (
                            <div>
                                <label className="block text-sm font-medium text-gray-700">
                                    Nom complet
                                </label>
                                <input
                                    name="name"
                                    type="text"
                                    required={isRegister}
                                    value={formData.name || ''}
                                    onChange={handleChange}
                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Votre nom"
                                />
                            </div>
                        )}

                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Email
                            </label>
                            <input
                                name="email"
                                type="email"
                                required
                                value={formData.email}
                                onChange={handleChange}
                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="votre@email.com"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Mot de passe
                            </label>
                            <input
                                name="password"
                                type="password"
                                required
                                value={formData.password}
                                onChange={handleChange}
                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Mot de passe"
                            />
                        </div>
                    </div>

                    <button
                        type="submit"
                        className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        {isRegister ? 'Créer le compte' : 'Se connecter'}
                    </button>
                </form>
            </div>
        </div>
    );
}

// Démarrage de l'app
console.log('Démarrage de MarquePage...');
const root = ReactDOM.createRoot(document.getElementById('app'));
root.render(<LoginPage />);
console.log('MarquePage démarrée !');