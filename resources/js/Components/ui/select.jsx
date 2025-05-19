import React from 'react';

export function Select({ children, className = '', ...props }) {
    return (
        <select
            className={`mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary ${className}`}
            {...props}
        >
            {children}
        </select>
    );
}

export function SelectTrigger({ children, className = '', ...props }) {
    return (
        <div className={`cursor-pointer border rounded px-3 py-2 ${className}`} {...props}>
            {children}
        </div>
    );
}

export function SelectValue({ children, className = '', ...props }) {
    return (
        <span className={className} {...props}>{children}</span>
    );
}

export function SelectContent({ children, className = '', ...props }) {
    return (
        <div className={`absolute z-10 mt-1 w-full bg-white border rounded shadow ${className}`} {...props}>
            {children}
        </div>
    );
}

export function SelectItem({ children, className = '', ...props }) {
    return (
        <div className={`px-4 py-2 hover:bg-primary hover:text-white cursor-pointer ${className}`} {...props}>
            {children}
        </div>
    );
} 