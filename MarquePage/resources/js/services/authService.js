import axios from 'axios';

const API_URL = '/api';

axios.defaults.baseURL = API_URL;
axios.defaults.headers.common['Content-Type'] = 'application/json';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const setAuthToken = (token) => {
    if (token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    } else {
        delete axios.defaults.headers.common['Authorization'];
    }
};

const authService = {
    setAuthToken,
    
    async login(email, password) {
        const response = await axios.post('/auth/login', {
            email,
            password
        });
        return response;
    },

    async register(name, email, password, password_confirmation) {
        const response = await axios.post('/auth/register', {
            name,
            email,
            password,
            password_confirmation
        });
        return response;
    },

    async logout() {
        const response = await axios.post('/auth/logout');
        return response;
    },

    async getUser() {
        const response = await axios.get('/auth/me');
        return response;
    },

    async refreshToken() {
        const response = await axios.post('/auth/refresh');
        return response;
    }
};

export default authService;