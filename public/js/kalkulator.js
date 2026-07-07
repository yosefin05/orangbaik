const NISAB_PENDAPATAN_BULANAN = 6859394;
const NISAB_PENDAPATAN_TAHUNAN = 82312725;
const KADAR_ZAKAT = 0.025;
const HARGA_EMAS_PER_GRAM = 82312725 / 85;
const formConfigs = {
    penghasilan: {
        title: "Zakat Penghasilan",
        subtitle: "Masukkan penghasilan bulanan Anda.",
        nishab: "Nishab zakat penghasilan 2024: Rp6.859.394/bulan (setara 85 gram emas/tahun).",
        fields: [
            {
                label: "Penghasilan Pokok",
                name: "gaji",
                placeholder: "Contoh: 7000000",
                rupiah: true,
                required: true,
            },
            {
                label: "Penghasilan Tambahan",
                name: "bonus",
                placeholder: "Contoh: 1000000",
                rupiah: true,
                required: false,
            },
        ],
    },

    emas: {
        title: "Zakat Emas",
        subtitle: "Masukkan jumlah emas yang dimiliki.",
        nishab: "Nishab emas adalah minimal 85 gram emas.",
        fields: [
            {
                label: "Jumlah Emas (gram)",
                name: "gram",
                placeholder: "Contoh:100",
                rupiah: false,
                required: true,
            },
            {
                label: "Harga Emas / gram",
                name: "harga_emas",
                placeholder: "Contoh:1900000",
                rupiah: true,
                required: true,
            },
        ],
    },

    tabungan: {
        title: "Zakat Tabungan",

        subtitle: "Masukkan total tabungan yang dimiliki.",

        nishab: "Minimal senilai 85 gram emas.",

        fields: [
            {
                label: "Saldo Tabungan",
                name: "saldo",
                placeholder: "Contoh: 100000000",
            },

            {
                label: "Bunga Bank",
                name: "bunga",
                placeholder: "0",
            },
        ],
    },

    perdagangan: {
        title: "Zakat Perdagangan",

        subtitle: "Masukkan aset usaha Anda.",

        nishab: "Minimal senilai 85 gram emas.",

        fields: [
            {
                label: "Modal",
                name: "modal",
                placeholder: "50000000",
            },

            {
                label: "Keuntungan",
                name: "untung",
                placeholder: "10000000",
            },

            {
                label: "Piutang",
                name: "piutang",
                placeholder: "0",
            },

            {
                label: "Kerugian",
                name: "rugi",
                placeholder: "0",
            },

            {
                label: "Hutang",
                name: "hutang",
                placeholder: "0",
            },
        ],
    },
};

function renderFields(type){

    const config=formConfigs[type];

    document.getElementById("form-title").innerText=config.title;

    document.getElementById("form-subtitle").innerText=config.subtitle;

    document.getElementById("nishab-info").innerText=config.nishab;


    const container=document.getElementById("form-fields");

    container.innerHTML="";


    config.fields.forEach(field=>{

        container.innerHTML+=`

        <div class="zakat-form-group">

            <label>
                ${field.label}
                ${field.required ? '<span style="color:red">*</span>' : ''}
            </label>


            <div class="rupiah-input">

                ${field.rupiah ? '<span>Rp</span>' : ''}

                <input
                    type="text"
                    name="${field.name}"
                    class="${field.rupiah ? 'format-rupiah':''}"
                    placeholder="${field.placeholder}"
                    ${field.required?'required':''}
                >

            </div>

        </div>

        `;

    });


    formatInputRupiah();

}

function formatRupiah(angka){

    return new Intl.NumberFormat('id-ID',{
        style:'currency',
        currency:'IDR',
        minimumFractionDigits:0
    }).format(angka);

}

function selectZakat(type, button) {
    document.getElementById("jenis").value = type;

    document
        .querySelectorAll(".zakat-tab")
        .forEach((tab) => tab.classList.remove("active"));

    button.classList.add("active");

    renderFields(type);
}

document.addEventListener("DOMContentLoaded", () => {
    renderFields("penghasilan");
});
