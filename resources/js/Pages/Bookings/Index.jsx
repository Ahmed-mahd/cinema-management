import React from 'react';
import { Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { motion } from 'framer-motion';

const Index = ({ auth, bookings }) => {
    const getStatusColor = (status) => {
        switch (status) {
            case 'active':
                return 'bg-green-100 text-green-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h2 className="text-2xl font-bold mb-6">My Bookings</h2>

                            {bookings.length === 0 ? (
                                <div className="text-center py-8">
                                    <p className="text-gray-600 mb-4">You haven't made any bookings yet.</p>
                                    <Link
                                        href={route('movies.index')}
                                        className="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors"
                                    >
                                        Browse Movies
                                    </Link>
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    {bookings.map((booking) => (
                                        <motion.div
                                            key={booking.id}
                                            initial={{ opacity: 0, y: 20 }}
                                            animate={{ opacity: 1, y: 0 }}
                                            className="border rounded-lg p-4 hover:shadow-md transition-shadow"
                                        >
                                            <div className="flex flex-col md:flex-row md:items-center md:justify-between">
                                                <div className="space-y-2">
                                                    <div className="flex items-center space-x-2">
                                                        <h3 className="text-lg font-medium">
                                                            {booking.showtime.movie.title}
                                                        </h3>
                                                        <span
                                                            className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(
                                                                booking.status
                                                            )}`}
                                                        >
                                                            {booking.status}
                                                        </span>
                                                    </div>
                                                    <div className="text-sm text-gray-600 space-y-1">
                                                        <p>
                                                            Showtime:{' '}
                                                            {new Date(
                                                                booking.showtime.start_time
                                                            ).toLocaleString()}
                                                        </p>
                                                        <p>Hall: {booking.showtime.hall.name}</p>
                                                        <p>
                                                            Seats:{' '}
                                                            {booking.seats
                                                                .map((seat) => seat.seat_number)
                                                                .join(', ')}
                                                        </p>
                                                        <p>
                                                            Total: ${booking.total_price.toFixed(2)}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="mt-4 md:mt-0 flex space-x-2">
                                                    {booking.status === 'active' && (
                                                        <Link
                                                            href={route('bookings.show', booking.id)}
                                                            className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none transition ease-in-out duration-150"
                                                        >
                                                            View Details
                                                        </Link>
                                                    )}
                                                    {booking.status === 'pending' && (
                                                        <button
                                                            onClick={() =>
                                                                window.location.href = route(
                                                                    'bookings.cancel',
                                                                    booking.id
                                                                )
                                                            }
                                                            className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none transition ease-in-out duration-150"
                                                        >
                                                            Cancel Booking
                                                        </button>
                                                    )}
                                                </div>
                                            </div>
                                        </motion.div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
};

export default Index; 