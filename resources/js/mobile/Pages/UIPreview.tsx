import React from 'react';
import MobileLayout from '../Layouts/MobileLayout';
import Button from '../Components/UI/Button';
import Card from '../Components/UI/Card';
import UserInfo from '../Components/UserInfo';

export default function UIPreview() {
    const dummyUser = {
        name: "Budi Santoso",
        email: "budi.santoso@example.com",
        role: "Supervisor",
    };

    return (
        <MobileLayout user={dummyUser} title="UI Preview" showBottomMenu={true}>
            <div className="space-y-6">
                
                {/* User Info Section */}
                <section>
                    <h2 className="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Profile Component</h2>
                    <UserInfo user={dummyUser} />
                </section>

                {/* Cards Section */}
                <section>
                    <h2 className="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Card Component</h2>
                    <div className="space-y-3">
                        <Card>
                            <h3 className="font-bold text-gray-800">Standard Card</h3>
                            <p className="text-sm text-gray-500 mt-1">This is a standard card with default padding (md).</p>
                        </Card>
                        
                        <Card padding="sm" onClick={() => alert('Card ditekan!')}>
                            <div className="flex justify-between items-center px-2 py-1">
                                <div>
                                    <h3 className="font-bold text-gray-800">Interactive Card</h3>
                                    <p className="text-xs text-gray-500">Tap me to see the effect</p>
                                </div>
                                <div className="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold">
                                    →
                                </div>
                            </div>
                        </Card>
                    </div>
                </section>

                {/* Buttons Section */}
                <section>
                    <h2 className="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Button Variants</h2>
                    <div className="space-y-3">
                        <Button fullWidth variant="primary">Primary Button</Button>
                        <Button fullWidth variant="secondary">Secondary Button</Button>
                        <Button fullWidth variant="outline">Outline Button</Button>
                        <Button fullWidth variant="ghost">Ghost Button</Button>
                        <Button fullWidth variant="danger">Danger Button</Button>
                        
                        <div className="flex space-x-3 pt-2">
                            <Button size="sm" variant="primary">Small</Button>
                            <Button size="md" variant="primary">Medium</Button>
                            <Button size="lg" variant="primary">Large</Button>
                        </div>

                        <Button fullWidth variant="primary" isLoading={true}>Loading State</Button>
                    </div>
                </section>

            </div>
        </MobileLayout>
    );
}
