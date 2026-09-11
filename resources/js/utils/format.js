export const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount);
};

export const formatDate = (timestamp) => {
    if (!timestamp) return '';

    return new Date(timestamp).toLocaleString('en-PH', {
        dateStyle: 'medium',
    });
};
