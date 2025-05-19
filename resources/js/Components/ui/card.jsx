import React from 'react';

export function Card({ children, className = '', ...props }) {
    return (
        <div className={`bg-white rounded-lg shadow ${className}`} {...props}>
            {children}
        </div>
    );
}

export function CardHeader({ children, className = '', ...props }) {
    return (
        <div className={`border-b px-4 py-2 font-semibold text-lg ${className}`} {...props}>
            {children}
        </div>
    );
}

export function CardTitle({ children, className = '', ...props }) {
    return (
        <h2 className={`text-xl font-bold mb-2 ${className}`} {...props}>
            {children}
        </h2>
    );
}

export function CardContent({ children, className = '', ...props }) {
    return (
        <div className={`p-4 ${className}`} {...props}>
            {children}
        </div>
    );
} 