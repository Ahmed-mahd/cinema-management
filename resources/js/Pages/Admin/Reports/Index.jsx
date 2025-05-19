import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { motion } from 'framer-motion';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement,
} from 'chart.js';
import { Line, Bar, Pie } from 'react-chartjs-2';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement
);

const StatCard = ({ title, value, icon, color }) => (
    <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className={`bg-white rounded-lg shadow p-6 ${color}`}
    >
        <div className="flex items-center">
            <div className="p-3 rounded-full bg-white bg-opacity-20">
                {icon}
            </div>
            <div className="ml-4">
                <p className="text-sm font-medium text-white opacity-80">{title}</p>
                <p className="text-2xl font-semibold text-white">{value}</p>
            </div>
        </div>
    </motion.div>
);

const Index = ({ auth, stats }) => {
    const [timeRange, setTimeRange] = useState('week');

    const revenueData = {
        labels: stats.revenue.labels,
        datasets: [
            {
                label: 'Revenue',
                data: stats.revenue.data,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1,
            },
        ],
    };

    const bookingsData = {
        labels: stats.bookings.labels,
        datasets: [
            {
                label: 'Bookings',
                data: stats.bookings.data,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
            },
        ],
    };

    const movieDistributionData = {
        labels: stats.movie_distribution.labels,
        datasets: [
            {
                data: stats.movie_distribution.data,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)',
                ],
            },
        ],
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Time Range Selector */}
                    <div className="mb-6">
                        <div className="flex space-x-4">
                            {['week', 'month', 'year'].map((range) => (
                                <button
                                    key={range}
                                    onClick={() => setTimeRange(range)}
                                    className={`px-4 py-2 rounded-lg text-sm font-medium ${
                                        timeRange === range
                                            ? 'bg-primary text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    }`}
                                >
                                    {range.charAt(0).toUpperCase() + range.slice(1)}
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Stats Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <StatCard
                            title="Total Revenue"
                            value={`$${stats.total_revenue.toFixed(2)}`}
                            color="bg-blue-600"
                            icon={
                                <svg
                                    className="w-6 h-6 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            }
                        />
                        <StatCard
                            title="Total Bookings"
                            value={stats.total_bookings}
                            color="bg-green-600"
                            icon={
                                <svg
                                    className="w-6 h-6 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                    />
                                </svg>
                            }
                        />
                        <StatCard
                            title="Average Ticket Price"
                            value={`$${stats.avg_ticket_price.toFixed(2)}`}
                            color="bg-purple-600"
                            icon={
                                <svg
                                    className="w-6 h-6 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
                                    />
                                </svg>
                            }
                        />
                        <StatCard
                            title="Occupancy Rate"
                            value={`${stats.occupancy_rate}%`}
                            color="bg-orange-600"
                            icon={
                                <svg
                                    className="w-6 h-6 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-4 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                    />
                                </svg>
                            }
                        />
                    </div>

                    {/* Charts Grid */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* Revenue Chart */}
                        <div className="bg-white rounded-lg shadow p-6">
                            <h3 className="text-lg font-semibold mb-4">Revenue Trend</h3>
                            <Line data={revenueData} />
                        </div>

                        {/* Bookings Chart */}
                        <div className="bg-white rounded-lg shadow p-6">
                            <h3 className="text-lg font-semibold mb-4">Booking Distribution</h3>
                            <Bar data={bookingsData} />
                        </div>

                        {/* Movie Distribution Chart */}
                        <div className="bg-white rounded-lg shadow p-6">
                            <h3 className="text-lg font-semibold mb-4">Movie Distribution</h3>
                            <Pie data={movieDistributionData} />
                        </div>

                        {/* Top Movies Table */}
                        <div className="bg-white rounded-lg shadow p-6">
                            <h3 className="text-lg font-semibold mb-4">Top Performing Movies</h3>
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th
                                                scope="col"
                                                className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Movie
                                            </th>
                                            <th
                                                scope="col"
                                                className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Revenue
                                            </th>
                                            <th
                                                scope="col"
                                                className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Bookings
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {stats.top_movies.map((movie) => (
                                            <tr key={movie.id}>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="flex items-center">
                                                        <div className="flex-shrink-0 h-10 w-10">
                                                            <img
                                                                className="h-10 w-10 rounded-lg object-cover"
                                                                src={movie.poster_url}
                                                                alt={movie.title}
                                                            />
                                                        </div>
                                                        <div className="ml-4">
                                                            <div className="text-sm font-medium text-gray-900">
                                                                {movie.title}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="text-sm text-gray-900">
                                                        ${movie.revenue.toFixed(2)}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="text-sm text-gray-900">
                                                        {movie.bookings}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
};

export default Index; 