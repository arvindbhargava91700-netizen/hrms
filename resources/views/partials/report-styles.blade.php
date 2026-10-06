<style>
/* ─── Report Card Styles ─── */
.report-filter-card { border-radius: 14px; background: #fff; border: 1px solid #e8ecf0; }
.report-filter-card .card-body { padding: 1.25rem 1.5rem; }
.report-stat-card { border-radius: 14px; border: none; background: linear-gradient(135deg, #f8fafc, #fff); box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.report-table thead th { background: #f1f5f9; color: #64748b; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; border: none; padding: 0.9rem 1rem; }
.report-table tbody tr:hover { background: #f8fafc; }
.report-table tbody td { padding: 0.75rem 1rem; font-size: 0.88rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.report-table tbody tr:last-child td { border-bottom: none; }
.report-actions .btn { border-radius: 10px; font-weight: 600; font-size: 0.84rem; padding: 0.5rem 1.1rem; }
.filter-label { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin-bottom: 0.35rem; }

/* ─── Print Styles ─── */
@media print {
    body * { visibility: hidden !important; }
    #print-area, #print-area * { visibility: visible !important; }
    #print-area { position: fixed; left: 0; top: 0; width: 100%; padding: 20px; }
    .d-print-none { display: none !important; }
    .report-table thead th { background: #f1f5f9 !important; -webkit-print-color-adjust: exact; }
    .badge { border: 1px solid #ccc; }
    .report-print-header { display: block !important; }
}
.report-print-header { display: none; }
</style>
