import React, { createContext, useContext, useState, useEffect } from 'react';
import authService from '../services/authService';

const AuthContext = createContext();

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
};

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    const [token, setToken] = useState(localStorage.getItem('token'));

    useEffect(() => {
        if (token) {
            authService.setAuthToken(token);
            loadUser();
        } else {
            setLoading(false);
        }
    }, []);

    const loadUser = async () => {
        try {
            const response = await authService.getUser();
            setUser(response.data.data);
        } catch (error) {
            console.error('Failed to load user:', error);
            logout();
        } finally {
            setLoading(false);
        }
    };

    const login = async (email, password) => {
        const response = await authService.login(email, password);
        const { token, user } = response.data.data;
        
        localStorage.setItem('token', token);
        authService.setAuthToken(token);
        setToken(token);
        setUser(user);
        
        return response;
    };

    const register = async (name, email, password, passwordConfirmation) => {
        const response = await authService.register(name, email, password, passwordConfirmation);
        const { token, user } = response.data.data;
        
        localStorage.setItem('token', token);
        authService.setAuthToken(token);
        setToken(token);
        setUser(user);
        
        return response;
    };

    const logout = () => {
        localStorage.removeItem('token');
        authService.setAuthToken(null);
        setToken(null);
        setUser(null);
    };

    const value = {
        user,
        token,
        login,
        register,
        logout,
        loading,
        isAuthenticated: !!user && !!token
    };

    return (
        <AuthContext.Provider value={value}>
            {children}
        </AuthContext.Provider>
    );
};