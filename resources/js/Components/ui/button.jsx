import React from 'react';

export function Button({ children, className = '', ...props }) {
    return (
        <button
            className={`px-4 py-2 rounded-lg bg-primary text-white hover:bg-primary-dark transition-colors ${className}`}
            {...props}
        >
            {children}
        </button>
    );
} 