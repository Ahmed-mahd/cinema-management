import { Link } from '@inertiajs/react';

export default function MovieCard({ movie }) {
    return (
        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6">
                {movie.poster_url && (
                    <img
                        src={movie.poster_url}
                        alt={movie.title}
                        className="w-full h-64 object-cover rounded-lg mb-4"
                    />
                )}
                <h3 className="text-lg font-semibold mb-2">{movie.title}</h3>
                <p className="text-gray-600 text-sm mb-2">{movie.genre}</p>
                <p className="text-gray-600 text-sm mb-4">
                    Duration: {movie.duration} minutes
                </p>
                <div className="flex justify-between items-center">
                    {movie.rating && (
                        <div className="text-yellow-500">
                            Rating: {movie.rating}/10
                        </div>
                    )}
                    <Link
                        href={route('movies.show', movie.id)}
                        className="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                    >
                        View Details
                    </Link>
                </div>
            </div>
        </div>
    );
} 