import { render } from '@wordpress/element';
import App from './components/App';
import './styles.css';

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('easycommerce-fakerpress-root');
    if (container) {
        render(<App />, container);
    }
});