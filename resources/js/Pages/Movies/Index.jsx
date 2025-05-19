import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ auth, movies }) {
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedGenre, setSelectedGenre] = useState('all');

    const genres = ['all', 'action', 'comedy', 'drama', 'horror', 'sci-fi', 'thriller'];

    const filteredMovies = movies.filter(movie => {
        const matchesSearch = movie.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            movie.description.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesGenre = selectedGenre === 'all' || movie.genre.toLowerCase() === selectedGenre;
        return matchesSearch && matchesGenre;
    });

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Movies
                </h2>
            }
        >
            <Head title="Movies" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Search and Filter Section */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div className="p-6">
                            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div className="flex-1">
                                    <div className="relative">
                                        <input
                                            type="text"
                                            placeholder="Search movies..."
                                            value={searchTerm}
                                            onChange={(e) => setSearchTerm(e.target.value)}
                                            className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                        />
                                        <svg
                                            className="absolute left-3 top-2.5 h-5 w-5 text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div className="flex gap-2">
                                    {genres.map((genre) => (
                                        <button
                                            key={genre}
                                            onClick={() => setSelectedGenre(genre)}
                                            className={`px-4 py-2 rounded-lg text-sm font-medium transition ${
                                                selectedGenre === genre
                                                    ? 'bg-red-600 text-white'
                                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                            }`}
                                        >
                                            {genre.charAt(0).toUpperCase() + genre.slice(1)}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Movies Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        {filteredMovies.map((movie) => (
                            <div key={movie.id} className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div className="relative">
                                    <img
                                        src={movie.poster_url}
                                        alt={movie.title}
                                        className="w-full h-64 object-cover"
                                    />
                                    <div className="absolute top-0 right-0 bg-red-600 text-white px-2 py-1 m-2 rounded">
                                        {movie.duration} min
                                    </div>
                                </div>
                                <div className="p-4">
                                    <h3 className="text-lg font-semibold text-gray-900 mb-2">
                                        {movie.title}
                                    </h3>
                                    <p className="text-sm text-gray-600 mb-4 line-clamp-2">
                                        {movie.description}
                                    </p>
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium text-gray-500">
                                            {movie.genre}
                                        </span>
                                        <Link
                                            href={route('movies.show', movie.id)}
                                            className="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
                                        >
                                            View Details
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>

                    {/* No Results Message */}
                    {filteredMovies.length === 0 && (
                        <div className="text-center py-12">
                            <svg
                                className="mx-auto h-12 w-12 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="2"
                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <h3 className="mt-2 text-sm font-medium text-gray-900">No movies found</h3>
                            <p className="mt-1 text-sm text-gray-500">
                                Try adjusting your search or filter to find what you're looking for.
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 