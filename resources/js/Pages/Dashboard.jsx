import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';

export default function Dashboard({ auth, recentBookings, upcomingShowtimes, totalMovies, totalBookings }) {
    const [activeTab, setActiveTab] = useState('overview');

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Welcome Section */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div className="p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <h3 className="text-2xl font-bold text-gray-900">
                                        Welcome back, {auth.user.name}!
                                    </h3>
                                    <p className="mt-1 text-gray-600">
                                        {auth.user.role === 'admin' 
                                            ? 'Manage your cinema system from here.'
                                            : 'Browse movies and book your tickets.'}
                                    </p>
                                </div>
                                <div className="flex space-x-4">
                                    <Link
                                        href={route('movies.index')}
                                        className="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
                                    >
                                        <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        Browse Movies
                                    </Link>
                                    {auth.user.role === 'admin' && (
                                        <Link
                                            href={route('admin.movies')}
                                            className="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition"
                                        >
                                            <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Admin Panel
                                        </Link>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Stats Section */}
                    {auth.user.role === 'admin' && (
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <div className="flex items-center">
                                        <div className="p-3 rounded-full bg-red-100 text-red-600">
                                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                                            </svg>
                                        </div>
                                        <div className="ml-4">
                                            <p className="text-sm font-medium text-gray-600">Total Movies</p>
                                            <p className="text-2xl font-semibold text-gray-900">{totalMovies}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <div className="flex items-center">
                                        <div className="p-3 rounded-full bg-green-100 text-green-600">
                                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <div className="ml-4">
                                            <p className="text-sm font-medium text-gray-600">Total Bookings</p>
                                            <p className="text-2xl font-semibold text-gray-900">{totalBookings}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <div className="flex items-center">
                                        <div className="p-3 rounded-full bg-blue-100 text-blue-600">
                                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div className="ml-4">
                                            <p className="text-sm font-medium text-gray-600">Upcoming Showtimes</p>
                                            <p className="text-2xl font-semibold text-gray-900">{upcomingShowtimes.length}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Tabs */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div className="border-b border-gray-200">
                            <nav className="flex -mb-px">
                                <button
                                    onClick={() => setActiveTab('overview')}
                                    className={`py-4 px-6 text-sm font-medium ${
                                        activeTab === 'overview'
                                            ? 'border-b-2 border-red-500 text-red-600'
                                            : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    }`}
                                >
                                    Overview
                                </button>
                                <button
                                    onClick={() => setActiveTab('bookings')}
                                    className={`py-4 px-6 text-sm font-medium ${
                                        activeTab === 'bookings'
                                            ? 'border-b-2 border-red-500 text-red-600'
                                            : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    }`}
                                >
                                    {auth.user.role === 'admin' ? 'All Bookings' : 'My Bookings'}
                                </button>
                                <button
                                    onClick={() => setActiveTab('showtimes')}
                                    className={`py-4 px-6 text-sm font-medium ${
                                        activeTab === 'showtimes'
                                            ? 'border-b-2 border-red-500 text-red-600'
                                            : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    }`}
                                >
                                    Upcoming Showtimes
                                </button>
                            </nav>
                        </div>

                        {/* Tab Content */}
                        <div className="p-6">
                            {activeTab === 'overview' && (
                                <div className="space-y-6">
                                    {auth.user.role !== 'admin' && (
                                        <div>
                                            <h3 className="text-lg font-semibold mb-4">Your Recent Bookings</h3>
                                            {recentBookings.length > 0 ? (
                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    {recentBookings.map((booking) => (
                                                        <div key={booking.id} className="bg-gray-50 rounded-lg p-4">
                                                            <div className="flex justify-between items-start">
                                                                <div>
                                                                    <h4 className="font-semibold text-gray-900">
                                                                        {booking.showtime.movie.title}
                                                                    </h4>
                                                                    <p className="text-sm text-gray-600">
                                                                        {new Date(booking.showtime.start_time).toLocaleString()}
                                                                    </p>
                                                                    <p className="text-sm text-gray-600">
                                                                        Hall: {booking.showtime.hall.name}
                                                                    </p>
                                                                </div>
                                                                <span className={`px-2 py-1 text-xs font-semibold rounded-full ${
                                                                    booking.status === 'confirmed'
                                                                        ? 'bg-green-100 text-green-800'
                                                                        : booking.status === 'pending'
                                                                        ? 'bg-yellow-100 text-yellow-800'
                                                                        : 'bg-red-100 text-red-800'
                                                                }`}>
                                                                    {booking.status}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    ))}
                                                </div>
                                            ) : (
                                                <p className="text-gray-600">You haven't made any bookings yet.</p>
                                            )}
                                        </div>
                                    )}
                                </div>
                            )}

                            {activeTab === 'bookings' && (
                                <div>
                                    <div className="flex justify-between items-center mb-4">
                                        <h3 className="text-lg font-semibold">
                                            {auth.user.role === 'admin' ? 'All Bookings' : 'My Bookings'}
                                        </h3>
                                        {auth.user.role === 'admin' ? (
                                            <Link
                                                href={route('admin.bookings')}
                                                className="text-red-600 hover:text-red-800"
                                            >
                                                View All
                                            </Link>
                                        ) : (
                                            <Link
                                                href={route('bookings.index')}
                                                className="text-red-600 hover:text-red-800"
                                            >
                                                View All
                                            </Link>
                                        )}
                                    </div>
                                    {/* Booking list content */}
                                </div>
                            )}

                            {activeTab === 'showtimes' && (
                                <div>
                                    <div className="flex justify-between items-center mb-4">
                                        <h3 className="text-lg font-semibold">Upcoming Showtimes</h3>
                                        <Link
                                            href={route('movies.index')}
                                            className="text-red-600 hover:text-red-800"
                                        >
                                            View All Movies
                                        </Link>
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                        {upcomingShowtimes.map((showtime) => (
                                            <div key={showtime.id} className="bg-gray-50 rounded-lg overflow-hidden">
                                                <div className="p-4">
                                                    <h4 className="font-semibold text-gray-900 mb-2">
                                                        {showtime.movie.title}
                                                    </h4>
                                                    <p className="text-sm text-gray-600 mb-2">
                                                        {new Date(showtime.start_time).toLocaleString()}
                                                    </p>
                                                    <p className="text-sm text-gray-600 mb-4">
                                                        Hall: {showtime.hall.name}
                                                    </p>
                                                    <Link
                                                        href={route('showtimes.show', showtime.id)}
                                                        className="inline-block w-full text-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
                                                    >
                                                        Book Tickets
                                                    </Link>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
