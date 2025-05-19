import React, { useState, useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import { motion } from 'framer-motion';

const SeatSelection = ({ showtime, onSeatsSelected }) => {
    const [selectedSeats, setSelectedSeats] = useState([]);
    const [availableSeats, setAvailableSeats] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const { data, get, processing } = useForm({
        showtime_id: showtime.id,
    });

    useEffect(() => {
        loadAvailableSeats();
    }, [showtime.id]);

    const loadAvailableSeats = () => {
        setLoading(true);
        get(route('seats.availability', { hall: showtime.hall_id }), {
            onSuccess: (response) => {
                setAvailableSeats(response.seats);
                setLoading(false);
            },
            onError: (errors) => {
                setError('Failed to load seats. Please try again.');
                setLoading(false);
            },
        });
    };

    const handleSeatClick = (seat) => {
        if (seat.status === 'booked') return;

        setSelectedSeats((prev) => {
            const isSelected = prev.find((s) => s.id === seat.id);
            if (isSelected) {
                return prev.filter((s) => s.id !== seat.id);
            }
            return [...prev, seat];
        });
    };

    useEffect(() => {
        onSeatsSelected(selectedSeats);
    }, [selectedSeats]);

    if (loading) {
        return (
            <div className="flex items-center justify-center p-8">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="bg-red-50 p-4 rounded-lg">
                <p className="text-red-600">{error}</p>
                <button
                    onClick={loadAvailableSeats}
                    className="mt-2 text-sm text-red-600 hover:text-red-800"
                >
                    Try Again
                </button>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div className="bg-gray-100 p-4 rounded-lg text-center">
                <h3 className="text-lg font-medium">Screen</h3>
            </div>

            <div className="grid grid-cols-8 gap-2">
                {availableSeats.map((seat) => (
                    <motion.button
                        key={seat.id}
                        whileHover={{ scale: 1.05 }}
                        whileTap={{ scale: 0.95 }}
                        onClick={() => handleSeatClick(seat)}
                        className={`
                            p-2 rounded-lg text-center text-sm
                            ${
                                seat.status === 'booked'
                                    ? 'bg-gray-300 cursor-not-allowed'
                                    : selectedSeats.find((s) => s.id === seat.id)
                                    ? 'bg-primary text-white'
                                    : 'bg-gray-200 hover:bg-gray-300'
                            }
                        `}
                        disabled={seat.status === 'booked'}
                    >
                        {seat.seat_number}
                    </motion.button>
                ))}
            </div>

            <div className="flex items-center justify-between text-sm">
                <div className="flex items-center space-x-4">
                    <div className="flex items-center">
                        <div className="w-4 h-4 bg-gray-200 rounded mr-2"></div>
                        <span>Available</span>
                    </div>
                    <div className="flex items-center">
                        <div className="w-4 h-4 bg-primary rounded mr-2"></div>
                        <span>Selected</span>
                    </div>
                    <div className="flex items-center">
                        <div className="w-4 h-4 bg-gray-300 rounded mr-2"></div>
                        <span>Booked</span>
                    </div>
                </div>
                <div className="text-right">
                    <p className="font-medium">
                        Selected: {selectedSeats.length} seats
                    </p>
                    <p className="text-gray-600">
                        Total: ${(selectedSeats.length * showtime.price).toFixed(2)}
                    </p>
                </div>
            </div>
        </div>
    );
};

export default SeatSelection; 