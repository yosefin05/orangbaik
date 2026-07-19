// public/js/campaign-type.js
document.addEventListener('DOMContentLoaded', function() {
    console.log('campaign-type.js loaded');
    
    const campaignTypeSelect = document.getElementById('campaign_type');
    const noteElement = document.getElementById('campaignTypeNote');

    if (campaignTypeSelect && noteElement) {
        campaignTypeSelect.addEventListener('change', function() {
            const value = this.value;
            if (value === 'emergency') {
                noteElement.innerHTML = `
                    <strong>🔥 Donasi Darurat</strong><br>
                    Campaign darurat memerlukan persetujuan admin sebelum ditampilkan di halaman utama.
                    Pastikan data campaign Anda lengkap dan akurat.
                    Campaign akan muncul di section "Darurat! Bantu Sekarang".
                `;
            } else if (value === 'sustainable') {
                noteElement.innerHTML = `
                    <strong>♻️ Donasi Berkelanjutan</strong><br>
                    Campaign berkelanjutan memerlukan persetujuan admin sebelum ditampilkan di halaman utama.
                    Campaign ini akan muncul di section "Pemberdayaan Berkelanjutan".
                    Pastikan data campaign Anda lengkap dan akurat.
                `;
            } else {
                noteElement.innerHTML = `
                    <strong>Catatan:</strong> Campaign darurat dan berkelanjutan memerlukan 
                    persetujuan admin sebelum ditampilkan di halaman utama. 
                    Campaign reguler langsung tampil di halaman donasi.
                `;
            }
        });
    }
});