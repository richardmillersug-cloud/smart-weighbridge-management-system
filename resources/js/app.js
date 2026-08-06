import './bootstrap';

import {
    Chart,
    LineController,
    BarController,
    LineElement,
    BarElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Filler,
    Legend,
    Tooltip,
} from 'chart.js';

Chart.register(
    LineController,
    BarController,
    LineElement,
    BarElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Filler,
    Legend,
    Tooltip,
);

Chart.defaults.color = '#8599af';
Chart.defaults.borderColor = 'rgba(47, 63, 82, 0.4)';
Chart.defaults.font.family = "'Inter', sans-serif";

window.Chart = Chart;
