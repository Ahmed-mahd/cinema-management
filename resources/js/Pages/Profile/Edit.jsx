import React from 'react';
import { useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';

const Edit = ({ auth }) => {
    const { data, setData, put, processing, errors, reset } = useForm({
        name: auth.user.name,
        email: auth.user.email,
        phone: auth.user.phone || '',
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('profile.update'), {
            onSuccess: () => reset('current_password', 'password', 'password_confirmation'),
        });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <section>
                            <header>
                                <h2 className="text-lg font-medium text-gray-900">
                                    Profile Information
                                </h2>
                                <p className="mt-1 text-sm text-gray-600">
                                    Update your account's profile information and email address.
                                </p>
                            </header>

                            <form onSubmit={submit} className="mt-6 space-y-6">
                                <div>
                                    <InputLabel htmlFor="name" value="Name" />
                                    <TextInput
                                        id="name"
                                        className="mt-1 block w-full"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                        autoComplete="name"
                                    />
                                    <InputError message={errors.name} className="mt-2" />
                                </div>

                                <div>
                                    <InputLabel htmlFor="email" value="Email" />
                                    <TextInput
                                        id="email"
                                        type="email"
                                        className="mt-1 block w-full"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        required
                                        autoComplete="username"
                                    />
                                    <InputError message={errors.email} className="mt-2" />
                                </div>

                                <div>
                                    <InputLabel htmlFor="phone" value="Phone Number" />
                                    <TextInput
                                        id="phone"
                                        type="tel"
                                        className="mt-1 block w-full"
                                        value={data.phone}
                                        onChange={(e) => setData('phone', e.target.value)}
                                        autoComplete="tel"
                                    />
                                    <InputError message={errors.phone} className="mt-2" />
                                </div>

                                <div className="flex items-center gap-4">
                                    <PrimaryButton disabled={processing}>Save</PrimaryButton>
                                </div>
                            </form>
                        </section>
                    </div>

                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <section className="space-y-6">
                            <header>
                                <h2 className="text-lg font-medium text-gray-900">
                                    Update Password
                                </h2>
                                <p className="mt-1 text-sm text-gray-600">
                                    Ensure your account is using a long, random password to stay
                                    secure.
                                </p>
                            </header>

                            <form onSubmit={submit} className="mt-6 space-y-6">
                                <div>
                                    <InputLabel
                                        htmlFor="current_password"
                                        value="Current Password"
                                    />
                                    <TextInput
                                        id="current_password"
                                        type="password"
                                        className="mt-1 block w-full"
                                        value={data.current_password}
                                        onChange={(e) =>
                                            setData('current_password', e.target.value)
                                        }
                                        autoComplete="current-password"
                                    />
                                    <InputError
                                        message={errors.current_password}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <InputLabel htmlFor="password" value="New Password" />
                                    <TextInput
                                        id="password"
                                        type="password"
                                        className="mt-1 block w-full"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        autoComplete="new-password"
                                    />
                                    <InputError message={errors.password} className="mt-2" />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="password_confirmation"
                                        value="Confirm Password"
                                    />
                                    <TextInput
                                        id="password_confirmation"
                                        type="password"
                                        className="mt-1 block w-full"
                                        value={data.password_confirmation}
                                        onChange={(e) =>
                                            setData('password_confirmation', e.target.value)
                                        }
                                        autoComplete="new-password"
                                    />
                                    <InputError
                                        message={errors.password_confirmation}
                                        className="mt-2"
                                    />
                                </div>

                                <div className="flex items-center gap-4">
                                    <PrimaryButton disabled={processing}>Save</PrimaryButton>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
};

export default Edit;
