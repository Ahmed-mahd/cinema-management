import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';

const Notification = ({ type = 'success', message, onClose }) => {
    const variants = {
        initial: { opacity: 0, y: -50 },
        animate: { opacity: 1, y: 0 },
        exit: { opacity: 0, y: -50 },
    };

    const getIcon = () => {
        switch (type) {
            case 'success':
                return (
                    <svg
                        className="w-6 h-6 text-green-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                );
            case 'error':
                return (
                    <svg
                        className="w-6 h-6 text-red-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                );
            case 'warning':
                return (
                    <svg
                        className="w-6 h-6 text-yellow-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        />
                    </svg>
                );
            default:
                return null;
        }
    };

    const getBackgroundColor = () => {
        switch (type) {
            case 'success':
                return 'bg-green-50';
            case 'error':
                return 'bg-red-50';
            case 'warning':
                return 'bg-yellow-50';
            default:
                return 'bg-gray-50';
        }
    };

    const getTextColor = () => {
        switch (type) {
            case 'success':
                return 'text-green-800';
            case 'error':
                return 'text-red-800';
            case 'warning':
                return 'text-yellow-800';
            default:
                return 'text-gray-800';
        }
    };

    return (
        <AnimatePresence>
            {message && (
                <motion.div
                    variants={variants}
                    initial="initial"
                    animate="animate"
                    exit="exit"
                    className={`fixed top-4 right-4 z-50 ${getBackgroundColor()} rounded-lg shadow-lg p-4 max-w-md`}
                >
                    <div className="flex items-start">
                        <div className="flex-shrink-0">{getIcon()}</div>
                        <div className="ml-3 w-0 flex-1">
                            <p className={`text-sm font-medium ${getTextColor()}`}>{message}</p>
                        </div>
                        <div className="ml-4 flex-shrink-0 flex">
                            <button
                                onClick={onClose}
                                className="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none"
                            >
                                <span className="sr-only">Close</span>
                                <svg
                                    className="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    );
};

export default Notification; 