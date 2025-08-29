import axios from 'axios';

const bookService = {
    async getBooks(params = {}) {
        const response = await axios.get('/books', { params });
        return response;
    },

    async getBook(id) {
        const response = await axios.get(`/books/${id}`);
        return response;
    },

    async createBook(bookData) {
        const formData = new FormData();
        
        Object.keys(bookData).forEach(key => {
            if (bookData[key] !== null && bookData[key] !== undefined) {
                formData.append(key, bookData[key]);
            }
        });

        const response = await axios.post('/books', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        return response;
    },

    async updateBook(id, bookData) {
        const formData = new FormData();
        
        Object.keys(bookData).forEach(key => {
            if (bookData[key] !== null && bookData[key] !== undefined) {
                formData.append(key, bookData[key]);
            }
        });
        
        formData.append('_method', 'PUT');

        const response = await axios.post(`/books/${id}`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        return response;
    },

    async deleteBook(id) {
        const response = await axios.delete(`/books/${id}`);
        return response;
    },

    async updateProgress(id, currentPage) {
        const response = await axios.put(`/books/${id}/progress`, {
            current_page: currentPage
        });
        return response;
    },

    async getBookmarks(bookId) {
        const response = await axios.get(`/books/${bookId}/bookmarks`);
        return response;
    },

    async createBookmark(bookId, bookmarkData) {
        const response = await axios.post(`/books/${bookId}/bookmarks`, bookmarkData);
        return response;
    },

    async updateBookmark(bookId, bookmarkId, bookmarkData) {
        const response = await axios.put(`/books/${bookId}/bookmarks/${bookmarkId}`, bookmarkData);
        return response;
    },

    async deleteBookmark(bookId, bookmarkId) {
        const response = await axios.delete(`/books/${bookId}/bookmarks/${bookmarkId}`);
        return response;
    },

    async getQuotes(bookId) {
        const response = await axios.get(`/books/${bookId}/quotes`);
        return response;
    },

    async createQuote(bookId, quoteData) {
        const response = await axios.post(`/books/${bookId}/quotes`, quoteData);
        return response;
    },

    async updateQuote(bookId, quoteId, quoteData) {
        const response = await axios.put(`/books/${bookId}/quotes/${quoteId}`, quoteData);
        return response;
    },

    async deleteQuote(bookId, quoteId) {
        const response = await axios.delete(`/books/${bookId}/quotes/${quoteId}`);
        return response;
    }
};

export default bookService;