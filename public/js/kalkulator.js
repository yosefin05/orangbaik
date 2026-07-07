const NISAB_PENDAPATAN_BULANAN = 6859394;
const NISAB_PENDAPATAN_TAHUNAN = 82312725;
const KADAR_ZAKAT = 0.025;
const HARGA_EMAS_PER_GRAM = 82312725 / 85;
const formConfigs = {
    penghasilan: {
        title: "Zakat Penghasilan",
        nishab: "Rp6.859.394/bulan (setara 85 gram emas per tahun).",
        sections: [
            {
                title: "2. Masukkan Penghasilan",
                subtitle:
                    "Masukkan total penghasilan bulanan yang Anda terima (dalam Rupiah).",

                fields: [
                    {
                        label: "Penghasilan Pokok (Rp)",
                        name: "gaji",
                        placeholder: "Contoh: 7000000",
                        rupiah: true,
                        required: true,
                    },
                ],
            },

            {
                title: "3. Informasi Tambahan (Opsional)",
                subtitle: "Masukkan penghasilan tambahan apabila ada.",

                fields: [
                    {
                        label: "Penghasilan Tambahan (Rp)",
                        name: "bonus",
                        placeholder: "Contoh: 1000000",
                        rupiah: true,
                        required: false,
                    },
                ],
            },
        ],
    },

    emas: {
        title: "Zakat Emas",
        nishab: "85 gram emas (BAZNAS RI 2024).",
        sections: [
            {
                title: "2. Masukkan Data Emas",
                subtitle: "Masukkan jumlah emas yang dimiliki.",

                fields: [
                    {
                        label: "Jumlah Emas (gram)",
                        name: "gram",
                        placeholder: "Contoh: 100",
                        rupiah: false,
                        required: true,
                    },

                    {
                        label: "Nilai Emas (Rp)",
                        name: "harga_emas",
                        placeholder: "Otomatis dihitung",
                        rupiah: true,
                        required: true,
                        readonly: true,
                    },
                ],
            },

            {
                title: "3. Informasi Tambahan (Opsional)",
                subtitle:
                    "Masukkan emas yang masih menjadi tanggungan atau belum dimiliki sepenuhnya apabila ada.",

                fields: [
                    {
                        label: "Pengurang (Rp)",
                        name: "pengurang",
                        placeholder: "Contoh: 0",
                        rupiah: true,
                        required: false,
                    },
                ],
            },
        ],
    },

    tabungan: {
        title: "Zakat Tabungan",
        nishab: "Minimal senilai 85 gram emas.",
        sections: [
            {
                title: "2. Masukkan Data Tabungan",
                subtitle: "Masukkan jumlah tabungan yang dimiliki saat ini.",

                fields: [
                    {
                        label: "Saldo Tabungan (Rp)",
                        name: "saldo",
                        placeholder: "Contoh: 100000000",
                        rupiah: true,
                        required: true,
                    },
                ],
            },

            {
                title: "3. Informasi Tambahan (Opsional)",
                subtitle:
                    "Masukkan bunga bank atau pengurang lainnya apabila ada.",

                fields: [
                    {
                        label: "Bunga Bank (Rp)",
                        name: "bunga",
                        placeholder: "0",
                        rupiah: true,
                        required: false,
                    },
                ],
            },
        ],
    },
    perdagangan: {
        title: "Zakat Perniagaan",
        nishab: "Minimal senilai 85 gram emas.",

        sections: [
            {
                title: "2. Masukkan Nilai Usaha",
                subtitle: "Masukkan nilai aset usaha yang dimiliki.",

                fields: [
                    {
                        label: "Modal (Rp)",
                        name: "modal",
                        placeholder: "50000000",
                        rupiah: true,
                        required: true,
                    },

                    {
                        label: "Keuntungan (Rp)",
                        name: "untung",
                        placeholder: "10000000",
                        rupiah: true,
                        required: true,
                    },

                    {
                        label: "Piutang (Rp)",
                        name: "piutang",
                        placeholder: "0",
                        rupiah: true,
                        required: false,
                    },
                ],
            },

            {
                title: "3. Informasi Tambahan (Opsional)",
                subtitle:
                    "Masukkan kewajiban usaha yang dapat mengurangi harta.",

                fields: [
                    {
                        label: "Kerugian (Rp)",
                        name: "rugi",
                        placeholder: "0",
                        rupiah: true,
                        required: false,
                    },

                    {
                        label: "Hutang (Rp)",
                        name: "hutang",
                        placeholder: "0",
                        rupiah: true,
                        required: false,
                    },
                ],
            },
        ],
    },
};

function formatInputRupiah() {
    document.querySelectorAll(".format-rupiah").forEach((input) => {
        input.oninput = function () {
            let angka = this.value.replace(/\D/g, "");

            if (!angka) {
                this.value = "";
                return;
            }

            this.value = new Intl.NumberFormat("id-ID").format(angka);
        };
    });
}

function hitungHargaEmas() {
    const gram = document.querySelector('input[name="gram"]');
    const harga = document.querySelector('input[name="harga_emas"]');

    if (!gram || !harga) return;

    gram.addEventListener("input", function () {
        const jumlahGram = parseFloat(this.value.replace(",", ".")) || 0;

        const total = jumlahGram * HARGA_EMAS_PER_GRAM;

        harga.value = new Intl.NumberFormat("id-ID").format(Math.round(total));
    });
}

function renderFields(type) {
    const config = formConfigs[type];

    // Header
    document.getElementById("form-title").textContent = config.title;
    document.getElementById("nishab-info").textContent = config.nishab;

    const container = document.getElementById("form-fields");
    container.innerHTML = "";

    config.sections.forEach((section) => {
        // Judul Section
        container.innerHTML += `
            <div class="form-section">
                <h4>${section.title}</h4>
                <p>${section.subtitle}</p>
            </div>
        `;

        // Semua field pada section
        section.fields.forEach((field) => {
            container.innerHTML += `
                <div class="zakat-form-group">

                    <label>
                        ${field.label}
                        ${field.required ? '<span class="required">*</span>' : ""}
                    </label>

                    <div class="rupiah-input">

                        ${
                            field.rupiah
                                ? '<span class="rupiah-label">Rp</span>'
                                : ""
                        }

                        <input
                            type="text"
                            name="${field.name}"
                            class="${field.rupiah ? "format-rupiah" : ""}"
                            placeholder="${field.placeholder}"
                            autocomplete="off"
                            ${field.required ? "required" : ""}
                            ${field.readonly ? "readonly" : ""}
                        >

                    </div>

                    <small>
                        ${
                            field.rupiah
                                ? "Masukkan nominal tanpa titik atau koma."
                                : "Masukkan sesuai satuan yang diminta."
                        }
                    </small>

                </div>
            `;
        });
    });

    formatInputRupiah();
    hitungHargaEmas();
}

function selectZakat(type, button) {
    document.getElementById("jenis").value = type;

    document.querySelectorAll(".zakat-tab").forEach((tab) => {
        tab.classList.remove("active");
    });

    button.classList.add("active");

    renderFields(type);

    // reset hasil lama kalau ada
    document.querySelectorAll(".format-rupiah").forEach((input) => {
        input.value = "";
    });
}

document.addEventListener("DOMContentLoaded", () => {
    const jenis = window.selectedZakat || "penghasilan";

    renderFields(jenis);

    const activeTab = document.querySelector(`.zakat-tab[onclick*="${jenis}"]`);

    if (activeTab) {
        document.querySelectorAll(".zakat-tab").forEach((tab) => {
            tab.classList.remove("active");
        });

        activeTab.classList.add("active");
    }
});
document.getElementById("zakatForm").addEventListener("submit", function () {
    document.querySelectorAll(".format-rupiah").forEach((input) => {
        input.value = input.value.replace(/\./g, "");
    });
});
