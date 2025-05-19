import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import SeatSelection from '@/Components/SeatSelection';
import { motion, AnimatePresence } from 'framer-motion';

const steps = [
    { id: 'seats', title: 'Select Seats' },
    { id: 'payment', title: 'Payment Details' },
    { id: 'confirmation', title: 'Confirmation' },
];

const Create = ({ auth, showtime, movie }) => {
    const [currentStep, setCurrentStep] = useState('seats');
    const [selectedSeats, setSelectedSeats] = useState([]);

    const { data, setData, post, processing, errors } = useForm({
        showtime_id: showtime.id,
        seat_ids: [],
        payment_method: '',
    });

    const handleSeatsSelected = (seats) => {
        setSelectedSeats(seats);
        setData('seat_ids', seats.map(seat => seat.id));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('bookings.store'), {
            onSuccess: () => {
                setCurrentStep('confirmation');
            },
        });
    };

    const renderStep = () => {
        switch (currentStep) {
            case 'seats':
                return (
                    <div className="space-y-6">
                        <div className="bg-white rounded-lg shadow p-6">
                            <h2 className="text-2xl font-bold mb-4">Select Your Seats</h2>
                            <SeatSelection
                                showtime={showtime}
                                onSeatsSelected={handleSeatsSelected}
                            />
                        </div>
                        <div className="flex justify-end">
                            <button
                                onClick={() => setCurrentStep('payment')}
                                disabled={selectedSeats.length === 0}
                                className="btn-primary"
                            >
                                Continue to Payment
                            </button>
                        </div>
                    </div>
                );

            case 'payment':
                return (
                    <div className="space-y-6">
                        <div className="bg-white rounded-lg shadow p-6">
                            <h2 className="text-2xl font-bold mb-4">Payment Details</h2>
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        Payment Method
                                    </label>
                                    <select
                                        value={data.payment_method}
                                        onChange={(e) => setData('payment_method', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                    >
                                        <option value="">Select payment method</option>
                                        <option value="card">Credit Card</option>
                                        <option value="cash">Cash</option>
                                        <option value="online">Online Payment</option>
                                    </select>
                                    {errors.payment_method && (
                                        <p className="mt-1 text-sm text-red-600">
                                            {errors.payment_method}
                                        </p>
                                    )}
                                </div>

                                <div className="bg-gray-50 p-4 rounded-lg">
                                    <h3 className="font-medium mb-2">Booking Summary</h3>
                                    <div className="space-y-2">
                                        <p>Movie: {movie.title}</p>
                                        <p>Showtime: {showtime.start_time}</p>
                                        <p>Seats: {selectedSeats.length}</p>
                                        <p className="font-bold">
                                            Total: ${(selectedSeats.length * showtime.price).toFixed(2)}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="flex justify-between">
                            <button
                                onClick={() => setCurrentStep('seats')}
                                className="btn-secondary"
                            >
                                Back
                            </button>
                            <button
                                onClick={handleSubmit}
                                disabled={processing || !data.payment_method}
                                className="btn-primary"
                            >
                                {processing ? 'Processing...' : 'Complete Booking'}
                            </button>
                        </div>
                    </div>
                );

            case 'confirmation':
                return (
                    <div className="bg-white rounded-lg shadow p-6 text-center">
                        <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg
                                className="w-8 h-8 text-green-600"
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
                        </div>
                        <h2 className="text-2xl font-bold mb-2">Booking Confirmed!</h2>
                        <p className="text-gray-600 mb-6">
                            Thank you for your booking. A confirmation email has been sent to your
                            email address.
                        </p>
                        <div className="space-y-4">
                            <a href={route('bookings.index')} className="btn-primary block">
                                View My Bookings
                            </a>
                            <a href={route('dashboard')} className="btn-secondary block">
                                Return to Dashboard
                            </a>
                        </div>
                    </div>
                );

            default:
                return null;
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="mb-8">
                        <div className="flex items-center justify-between">
                            {steps.map((step, index) => (
                                <React.Fragment key={step.id}>
                                    <div className="flex items-center">
                                        <div
                                            className={`
                                                w-8 h-8 rounded-full flex items-center justify-center
                                                ${
                                                    currentStep === step.id
                                                        ? 'bg-primary text-white'
                                                        : 'bg-gray-200'
                                                }
                                            `}
                                        >
                                            {index + 1}
                                        </div>
                                        <span className="ml-2 text-sm font-medium">{step.title}</span>
                                    </div>
                                    {index < steps.length - 1 && (
                                        <div className="flex-1 h-0.5 bg-gray-200 mx-4"></div>
                                    )}
                                </React.Fragment>
                            ))}
                        </div>
                    </div>

                    <AnimatePresence mode="wait">
                        <motion.div
                            key={currentStep}
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -20 }}
                            transition={{ duration: 0.2 }}
                        >
                            {renderStep()}
                        </motion.div>
                    </AnimatePresence>
                </div>
            </div>
        </AuthenticatedLayout>
    );
};

export default Create; 