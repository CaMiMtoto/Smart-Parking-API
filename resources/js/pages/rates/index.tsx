import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import AppLayout from '@/layouts/app-layout';

export default function index({rates:Array}) {
    return (
        <AppLayout>
            <Head title="index" />
            <Heading title="Settings" description="Manage your profile and account settings" />
        </AppLayout>
    );
}
