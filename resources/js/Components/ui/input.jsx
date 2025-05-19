import React from 'react';

export function Input({ className = '', ...props }) {
    return (
        <input
            className={`mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary ${className}`}
            {...props}
        />
    );
} 