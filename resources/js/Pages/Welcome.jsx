import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import ApplicationLogo from '@/Components/ApplicationLogo';

export default function Welcome({ auth, canLogin, canRegister, laravelVersion, phpVersion }) {
    const [currentSlide, setCurrentSlide] = useState(0);
    const featuredMovies = [
        {
            title: "Experience the Magic of Cinema",
            description: "Book your tickets online and enjoy the latest blockbusters in our state-of-the-art theaters.",
            image: "https://images.unsplash.com/photo-1536440136628-849c177e76a1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1925&q=80"
        },
        {
            title: "Comfortable Seating",
            description: "Enjoy your movie in our premium, reclining seats with ample legroom.",
            image: "https://images.unsplash.com/photo-1579548122080-c35fd6820ecb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80"
        },
        {
            title: "Latest Technology",
            description: "Experience movies in stunning 4K resolution with Dolby Atmos sound.",
            image: "https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80"
        }
    ];

    useEffect(() => {
        const timer = setInterval(() => {
            setCurrentSlide((prev) => (prev + 1) % featuredMovies.length);
        }, 5000);
        return () => clearInterval(timer);
    }, []);

    const handleDashboardClick = (e) => {
        e.preventDefault();
        router.visit(route('dashboard'));
    };

    return (
        <>
            <Head title="Welcome" />
            <div className="relative min-h-screen bg-gray-100">
                {/* Navigation */}
                <nav className="absolute top-0 left-0 right-0 z-50 bg-transparent">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex justify-between items-center h-16">
                            <div className="flex-shrink-0">
                                <ApplicationLogo className="block h-9 w-auto fill-current text-white" />
                            </div>
                            <div className="flex items-center space-x-4">
                                {auth.user ? (
                                    <>
                                        <Link
                                            href={route('dashboard')}
                                            className="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium"
                                        >
                                            Dashboard
                                        </Link>
                                        {auth.user.role === 'admin' && (
                                            <Link
                                                href={route('admin.movies')}
                                                className="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium"
                                            >
                                                Admin Panel
                                            </Link>
                                        )}
                                    </>
                                ) : (
                                    <>
                                        {canLogin && (
                                            <Link
                                                href={route('login')}
                                                className="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium"
                                            >
                                                Login
                                            </Link>
                                        )}
                                        {canRegister && (
                                            <Link
                                                href={route('register')}
                                                className="bg-red-600 text-white hover:bg-red-700 px-3 py-2 rounded-md text-sm font-medium"
                                            >
                                                Register
                                            </Link>
                                        )}
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </nav>

                {/* Hero Section */}
                <div className="relative h-screen">
                    {featuredMovies.map((movie, index) => (
                        <div
                            key={index}
                            className={`absolute inset-0 transition-opacity duration-1000 ${
                                index === currentSlide ? 'opacity-100' : 'opacity-0'
                            }`}
                        >
                            <div
                                className="absolute inset-0 bg-cover bg-center"
                                style={{ backgroundImage: `url(${movie.image})` }}
                            >
                                <div className="absolute inset-0 bg-black bg-opacity-50" />
                            </div>
                            <div className="relative h-full flex items-center justify-center text-center text-white">
                                <div className="max-w-3xl px-4">
                                    <h1 className="text-5xl font-bold mb-4">{movie.title}</h1>
                                    <p className="text-xl mb-8">{movie.description}</p>
                                    {auth.user ? (
                                        <Link
                                            href={route('dashboard')}
                                            className="inline-block bg-red-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-red-700 transition"
                                        >
                                            Go to Dashboard
                                        </Link>
                                    ) : (
                                        <div className="space-x-4">
                                            <Link
                                                href={route('login')}
                                                className="inline-block bg-red-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-red-700 transition"
                                            >
                                                Login
                                            </Link>
                                            <Link
                                                href={route('register')}
                                                className="inline-block bg-white text-red-600 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-100 transition"
                                            >
                                                Register
                                            </Link>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Features Section */}
                <div className="py-16 bg-white">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="text-center mb-12">
                            <h2 className="text-3xl font-bold text-gray-900">Why Choose Us?</h2>
                        </div>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div className="text-center p-6">
                                <div className="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg className="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 className="text-xl font-semibold mb-2">Premium Experience</h3>
                                <p className="text-gray-600">Enjoy movies in our state-of-the-art theaters with the latest technology.</p>
                            </div>
                            <div className="text-center p-6">
                                <div className="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg className="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 className="text-xl font-semibold mb-2">Easy Booking</h3>
                                <p className="text-gray-600">Book your tickets online in advance and skip the queue.</p>
                            </div>
                            <div className="text-center p-6">
                                <div className="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg className="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                    </svg>
                                </div>
                                <h3 className="text-xl font-semibold mb-2">Best Value</h3>
                                <p className="text-gray-600">Get the best value for your money with our competitive pricing.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Footer */}
                <footer className="bg-gray-900 text-white py-8">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex justify-between items-center">
                            <div>
                                <ApplicationLogo className="block h-9 w-auto fill-current text-white" />
                            </div>
                            <div className="text-sm">
                                © {new Date().getFullYear()} Cinema Management System. All rights reserved.
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
