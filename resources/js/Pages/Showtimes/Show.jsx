import { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, showtime, bookedSeats }) {
    const [selectedSeats, setSelectedSeats] = useState([]);
    const { data, setData, post, processing, errors } = useForm({
        seat_ids: [],
    });

    const handleSeatClick = (seatId) => {
        if (bookedSeats.includes(seatId)) return;

        setSelectedSeats(prev => {
            if (prev.includes(seatId)) {
                return prev.filter(id => id !== seatId);
            } else {
                return [...prev, seatId];
            }
        });
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        setData('seat_ids', selectedSeats);
        post(route('bookings.store', { showtime: showtime.id }));
    };

    const calculateTotal = () => {
        return selectedSeats.length * showtime.price_per_seat;
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Book Tickets
                </h2>
            }
        >
            <Head title="Book Tickets" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {/* Movie and Showtime Details */}
                            <div className="mb-8">
                                <div className="flex flex-col md:flex-row gap-8">
                                    <div className="w-full md:w-1/3">
                                        <img
                                            src={showtime.movie.poster_url}
                                            alt={showtime.movie.title}
                                            className="w-full rounded-lg shadow-lg"
                                        />
                                    </div>
                                    <div className="w-full md:w-2/3">
                                        <h1 className="text-3xl font-bold text-gray-900 mb-4">
                                            {showtime.movie.title}
                                        </h1>
                                        <div className="space-y-4">
                                            <div className="flex items-center text-gray-600">
                                                <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>{new Date(showtime.start_time).toLocaleString()}</span>
                                            </div>
                                            <div className="flex items-center text-gray-600">
                                                <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                                <span>Hall: {showtime.hall.name}</span>
                                            </div>
                                            <div className="flex items-center text-gray-600">
                                                <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>Price per seat: ${showtime.price_per_seat}</span>
                                            </div>
                                        </div>
                                        <p className="mt-4 text-gray-600">
                                            {showtime.movie.description}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Seat Selection */}
                            <div className="mb-8">
                                <h2 className="text-xl font-semibold text-gray-900 mb-4">Select Seats</h2>
                                <div className="bg-gray-100 p-6 rounded-lg">
                                    <div className="flex justify-center mb-4">
                                        <div className="w-1/3 h-4 bg-gray-400 rounded-t-lg"></div>
                                    </div>
                                    <div className="grid grid-cols-8 gap-2">
                                        {showtime.hall.seats.map((seat) => (
                                            <button
                                                key={seat.id}
                                                onClick={() => handleSeatClick(seat.id)}
                                                disabled={bookedSeats.includes(seat.id)}
                                                className={`p-2 rounded-lg text-center text-sm font-medium transition ${
                                                    bookedSeats.includes(seat.id)
                                                        ? 'bg-gray-300 cursor-not-allowed'
                                                        : selectedSeats.includes(seat.id)
                                                        ? 'bg-red-600 text-white'
                                                        : 'bg-white hover:bg-gray-200'
                                                }`}
                                            >
                                                {seat.seat_number}
                                            </button>
                                        ))}
                                    </div>
                                    <div className="mt-4 flex justify-center space-x-4">
                                        <div className="flex items-center">
                                            <div className="w-4 h-4 bg-white rounded-lg mr-2"></div>
                                            <span className="text-sm text-gray-600">Available</span>
                                        </div>
                                        <div className="flex items-center">
                                            <div className="w-4 h-4 bg-red-600 rounded-lg mr-2"></div>
                                            <span className="text-sm text-gray-600">Selected</span>
                                        </div>
                                        <div className="flex items-center">
                                            <div className="w-4 h-4 bg-gray-300 rounded-lg mr-2"></div>
                                            <span className="text-sm text-gray-600">Booked</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Booking Summary */}
                            <div className="bg-gray-50 p-6 rounded-lg">
                                <h2 className="text-xl font-semibold text-gray-900 mb-4">Booking Summary</h2>
                                <div className="space-y-4">
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Selected Seats:</span>
                                        <span className="font-medium">{selectedSeats.length}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Price per Seat:</span>
                                        <span className="font-medium">${showtime.price_per_seat}</span>
                                    </div>
                                    <div className="border-t pt-4">
                                        <div className="flex justify-between">
                                            <span className="text-lg font-semibold">Total:</span>
                                            <span className="text-lg font-semibold">${calculateTotal()}</span>
                                        </div>
                                    </div>
                                    <form onSubmit={handleSubmit}>
                                        <button
                                            type="submit"
                                            disabled={processing || selectedSeats.length === 0}
                                            className={`w-full py-3 px-4 rounded-lg text-white font-medium ${
                                                processing || selectedSeats.length === 0
                                                    ? 'bg-gray-400 cursor-not-allowed'
                                                    : 'bg-red-600 hover:bg-red-700'
                                            }`}
                                        >
                                            {processing ? 'Processing...' : 'Book Now'}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 