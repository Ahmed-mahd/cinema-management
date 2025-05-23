import React from 'react';

const LoadingState = ({ text = 'Loading...' }) => {
    return (
        <div className="flex flex-col items-center justify-center p-8">
            <div className="relative">
                <div className="w-12 h-12 rounded-full absolute border-4 border-gray-200"></div>
                <div className="w-12 h-12 rounded-full animate-spin absolute border-4 border-primary border-t-transparent"></div>
            </div>
            <p className="mt-4 text-gray-600">{text}</p>
        </div>
    );
};

export const LoadingOverlay = ({ text = 'Loading...' }) => {
    return (
        <div className="fixed inset-0 bg-white bg-opacity-75 z-50 flex items-center justify-center">
            <div className="text-center">
                <div className="relative inline-block">
                    <div className="w-12 h-12 rounded-full absolute border-4 border-gray-200"></div>
                    <div className="w-12 h-12 rounded-full animate-spin absolute border-4 border-primary border-t-transparent"></div>
                </div>
                <p className="mt-4 text-gray-600">{text}</p>
            </div>
        </div>
    );
};

export const LoadingButton = ({ text = 'Loading...', className = '' }) => {
    return (
        <button
            disabled
            className={`inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary opacity-75 cursor-not-allowed ${className}`}
        >
            <svg
                className="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    className="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    strokeWidth="4"
                ></circle>
                <path
                    className="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
            </svg>
            {text}
        </button>
    );
};

export default LoadingState; 