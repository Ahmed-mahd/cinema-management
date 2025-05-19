import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Search, User, Ticket, Calendar, DollarSign } from 'lucide-react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';

export default function Index({ auth, bookings }) {
    const [searchQuery, setSearchQuery] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');

    const updateBookingStatus = (bookingId, newStatus) => {
        router.put(route('admin.bookings.update', bookingId), {
            status: newStatus
        });
    };

    const filteredBookings = bookings.filter(booking => {
        const matchesSearch = 
            booking.user.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
            booking.showtime.movie.title.toLowerCase().includes(searchQuery.toLowerCase());
        
        const matchesStatus = statusFilter === 'all' || booking.status === statusFilter;
        
        return matchesSearch && matchesStatus;
    });

    const getStatusBadgeVariant = (status) => {
        switch (status) {
            case 'confirmed':
                return 'success';
            case 'pending':
                return 'warning';
            case 'cancelled':
                return 'destructive';
            default:
                return 'secondary';
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Manage Bookings" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="grid gap-6">
                        {/* Stats Cards */}
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">Total Bookings</CardTitle>
                                    <Ticket className="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">{bookings.length}</div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">Confirmed Bookings</CardTitle>
                                    <Badge variant="success">Confirmed</Badge>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">
                                        {bookings.filter(b => b.status === 'confirmed').length}
                                    </div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">Pending Bookings</CardTitle>
                                    <Badge variant="warning">Pending</Badge>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">
                                        {bookings.filter(b => b.status === 'pending').length}
                                    </div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
                                    <DollarSign className="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">
                                        ${bookings.reduce((sum, booking) => sum + booking.total_amount, 0).toFixed(2)}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Main Content */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Bookings Management</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="flex flex-col md:flex-row gap-4 mb-4">
                                    <div className="relative flex-1">
                                        <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                        <Input
                                            placeholder="Search bookings..."
                                            value={searchQuery}
                                            onChange={(e) => setSearchQuery(e.target.value)}
                                            className="pl-8"
                                        />
                                    </div>
                                    <Select
                                        value={statusFilter}
                                        onValueChange={setStatusFilter}
                                    >
                                        <SelectTrigger className="w-[180px]">
                                            <SelectValue placeholder="Filter by status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Status</SelectItem>
                                            <SelectItem value="confirmed">Confirmed</SelectItem>
                                            <SelectItem value="pending">Pending</SelectItem>
                                            <SelectItem value="cancelled">Cancelled</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                {filteredBookings.length === 0 ? (
                                    <Alert>
                                        <AlertDescription>
                                            No bookings found matching your search criteria.
                                        </AlertDescription>
                                    </Alert>
                                ) : (
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Customer</TableHead>
                                                <TableHead>Movie</TableHead>
                                                <TableHead>Showtime</TableHead>
                                                <TableHead>Seats</TableHead>
                                                <TableHead>Amount</TableHead>
                                                <TableHead>Status</TableHead>
                                                <TableHead>Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {filteredBookings.map((booking) => (
                                                <TableRow key={booking.id}>
                                                    <TableCell>
                                                        <div className="flex items-center gap-2">
                                                            <User className="h-4 w-4 text-muted-foreground" />
                                                            <span className="font-medium">{booking.user.name}</span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell className="font-medium">
                                                        {booking.showtime.movie.title}
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="flex items-center gap-2">
                                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                                            <span>
                                                                {new Date(booking.showtime.date).toLocaleDateString()}
                                                                <br />
                                                                {booking.showtime.start_time}
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        {booking.seats.map(seat => (
                                                            <Badge key={seat.id} variant="secondary" className="mr-1">
                                                                {seat.seat_number}
                                                            </Badge>
                                                        ))}
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="flex items-center gap-2">
                                                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                                                            <span>${booking.total_amount}</span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge variant={getStatusBadgeVariant(booking.status)}>
                                                            {booking.status}
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="flex space-x-2">
                                                            {booking.status === 'pending' && (
                                                                <>
                                                                    <Button
                                                                        variant="outline"
                                                                        size="sm"
                                                                        onClick={() => updateBookingStatus(booking.id, 'confirmed')}
                                                                    >
                                                                        Confirm
                                                                    </Button>
                                                                    <Button
                                                                        variant="destructive"
                                                                        size="sm"
                                                                        onClick={() => updateBookingStatus(booking.id, 'cancelled')}
                                                                    >
                                                                        Cancel
                                                                    </Button>
                                                                </>
                                                            )}
                                                            {booking.status === 'confirmed' && (
                                                                <Button
                                                                    variant="destructive"
                                                                    size="sm"
                                                                    onClick={() => updateBookingStatus(booking.id, 'cancelled')}
                                                                >
                                                                    Cancel
                                                                </Button>
                                                            )}
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
