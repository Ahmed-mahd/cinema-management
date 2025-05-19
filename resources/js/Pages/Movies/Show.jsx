import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { motion } from 'framer-motion';

const Show = ({ auth, movie, showtimes }) => {
    const [selectedDate, setSelectedDate] = useState(new Date());

    const formatDate = (date) => {
        return new Date(date).toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
        });
    };

    const getDates = () => {
        const dates = [];
        for (let i = 0; i < 7; i++) {
            const date = new Date();
            date.setDate(date.getDate() + i);
            dates.push(date);
        }
        return dates;
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                                {/* Movie Poster */}
                                <div className="md:col-span-1">
                                    <img
                                        src={movie.poster_url}
                                        alt={movie.title}
                                        className="w-full rounded-lg shadow-lg"
                                    />
                                </div>

                                {/* Movie Details */}
                                <div className="md:col-span-2">
                                    <h1 className="text-3xl font-bold mb-4">{movie.title}</h1>
                                    <div className="space-y-4">
                                        <p className="text-gray-600">{movie.description}</p>
                                        <div className="grid grid-cols-2 gap-4">
                                            <div>
                                                <h3 className="font-medium text-gray-700">Duration</h3>
                                                <p>{movie.duration} minutes</p>
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-gray-700">Genre</h3>
                                                <p>{movie.genre}</p>
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-gray-700">Rating</h3>
                                                <p>{movie.rating}/10</p>
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-gray-700">Language</h3>
                                                <p>{movie.language}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Showtimes Section */}
                            <div className="mt-8">
                                <h2 className="text-2xl font-bold mb-4">Select Showtime</h2>
                                
                                {/* Date Selection */}
                                <div className="flex space-x-4 mb-6 overflow-x-auto pb-2">
                                    {getDates().map((date) => (
                                        <button
                                            key={date.toISOString()}
                                            onClick={() => setSelectedDate(date)}
                                            className={`
                                                px-4 py-2 rounded-lg text-sm font-medium
                                                ${
                                                    date.toDateString() === selectedDate.toDateString()
                                                        ? 'bg-primary text-white'
                                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                                }
                                            `}
                                        >
                                            {formatDate(date)}
                                        </button>
                                    ))}
                                </div>

                                {/* Showtimes Grid */}
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    {showtimes
                                        .filter(
                                            (showtime) =>
                                                new Date(showtime.start_time).toDateString() ===
                                                selectedDate.toDateString()
                                        )
                                        .map((showtime) => (
                                            <motion.div
                                                key={showtime.id}
                                                whileHover={{ scale: 1.02 }}
                                                className="bg-gray-50 rounded-lg p-4"
                                            >
                                                <p className="font-medium">
                                                    {new Date(showtime.start_time).toLocaleTimeString(
                                                        'en-US',
                                                        {
                                                            hour: '2-digit',
                                                            minute: '2-digit',
                                                        }
                                                    )}
                                                </p>
                                                <p className="text-sm text-gray-600">
                                                    Hall {showtime.hall.name}
                                                </p>
                                                <p className="text-sm text-gray-600">
                                                    ${showtime.price}
                                                </p>
                                                <Link
                                                    href={route('bookings.create', {
                                                        showtime: showtime.id,
                                                    })}
                                                    className="mt-2 block w-full text-center bg-primary text-white py-2 rounded-lg hover:bg-primary-dark transition-colors"
                                                >
                                                    Book Now
                                                </Link>
                                            </motion.div>
                                        ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
};

export default Show; 