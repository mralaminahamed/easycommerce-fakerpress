import { createRoot } from '@wordpress/element';
import App from './components/App';
import './styles.css';

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('easycommerce-fakerpress-root');
    if (container) {
        const root = createRoot(container);
        root.render(<App />);
    }
});
