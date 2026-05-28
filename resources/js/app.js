import './bootstrap';
import $ from 'jquery';
import select2 from 'select2';
// 1. Importa ApexCharts
import ApexCharts from 'apexcharts';
window.ApexCharts = ApexCharts;
window.$ = $;
// 2. Hazlo global asignándolo al objeto window
select2();

import "/node_modules/select2/dist/css/select2.css";
import '../css/app.css';
