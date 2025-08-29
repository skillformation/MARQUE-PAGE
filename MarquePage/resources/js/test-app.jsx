import './bootstrap';
import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';

// Version simplifiée pour tester React
function SimpleApp() {
    return (
        <div style={{ padding: '20px', fontFamily: 'Arial' }}>
            <h1 style={{ color: 'blue' }}>MarquePage - React Test</h1>
            <p>React fonctionne !</p>
            <AuthProvider>
                <div style={{ marginTop: '20px', padding: '10px', backgroundColor: '#f0f0f0' }}>
                    <p>AuthProvider chargé avec succès</p>
                </div>
            </AuthProvider>
        </div>
    );
}

console.log('Démarrage de React...');
const root = ReactDOM.createRoot(document.getElementById('app'));
root.render(
    <React.StrictMode>
        <BrowserRouter>
            <SimpleApp />
        </BrowserRouter>
    </React.StrictMode>
);
console.log('React démarré !');