import React from 'react';
import { createRoot } from 'react-dom/client';
import { ThemeProvider } from '@material-tailwind/react';
import Home from './Home';

if (document.getElementById('app')) {
    const container = document.getElementById('app');
    const root = createRoot(container);

    root.render(
        <ThemeProvider>
            <Home />
        </ThemeProvider>
    );
}