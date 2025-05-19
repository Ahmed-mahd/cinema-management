import React from 'react';

export function Alert({ children, type = 'info', className = '', ...props }) {
    const base = 'p-4 rounded-lg mb-4';
    const typeClass =
        type === 'success'
            ? 'bg-green-100 text-green-800'
            : type === 'error'
            ? 'bg-red-100 text-red-800'
            : type === 'warning'
            ? 'bg-yellow-100 text-yellow-800'
            : 'bg-blue-100 text-blue-800';
    return (
        <div className={`${base} ${typeClass} ${className}`} {...props}>
            {children}
        </div>
    );
}

export function AlertDescription({ children, className = '', ...props }) {
    return (
        <div className={`mt-2 text-sm ${className}`} {...props}>
            {children}
        </div>
    );
} 