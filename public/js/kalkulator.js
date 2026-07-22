const HARGA_EMAS = 968385;
const NISAB_EMAS = 85;
const NISAB_TAHUNAN = HARGA_EMAS * NISAB_EMAS;
const NISAB_BULANAN = NISAB_TAHUNAN / 12;

document.addEventListener("DOMContentLoaded", function () {
    const zakatData = {
        penghasilan: {
            title: "Zakat Penghasilan",
            percent: 2.5,
            nisab: "Nishab zakat penghasilan 2024: Rp6.859.394/bulan (setara 85 gram emas/tahun).",
            note: "Zakat penghasilan dihitung dari pendapatan bersih.",
            law: "Zakat penghasilan umumnya dihitung sebesar 2,5% dari pendapatan bersih yang telah mencapai nisab.",
            fields: [
                {
                    label: "Pendapatan Bulanan",
                    name: "penghasilan",
                    placeholder: "0",
                    type: "money",
                    role: "asset",
                    help: "Contoh: 5.000.000",
                },
                {
                    label: "Pendapatan Lain",
                    name: "pendapatan_lain",
                    placeholder: "0",
                    type: "money",
                    role: "asset",
                    help: "Bonus, komisi, atau penghasilan tambahan.",
                },
                {
                    label: "Kebutuhan / Hutang Jatuh Tempo",
                    name: "hutang",
                    placeholder: "0",
                    type: "money",
                    role: "debt",
                    help: "Isi 0 jika tidak ada.",
                    full: true,
                },
            ],
        },

        emas: {
            title: "Zakat Emas",
            percent: 2.5,
            nisab: "Nishab zakat penghasilan 2024: Rp6.859.394/bulan (setara 85 gram emas/tahun).",
            note: "Zakat emas dihitung dari total nilai emas yang dimiliki.",
            law: "Zakat emas dikenakan apabila emas yang dimiliki telah mencapai nisab dan memenuhi ketentuan haul.",
            fields: [
                {
                    label: "Berat Emas",
                    name: "berat_emas",
                    placeholder: "0",
                    type: "number",
                    role: "gold_weight",
                    help: "Masukkan berat emas dalam gram.",
                },
                {
                    label: "Harga Emas per Gram",
                    name: "harga_emas",
                    placeholder: "0",
                    type: "money",
                    role: "gold_price",
                    help: "Contoh: 1.200.000",
                },
                {
                    label: "Hutang Terkait Harta",
                    name: "hutang",
                    placeholder: "0",
                    type: "money",
                    role: "debt",
                    help: "Isi 0 jika tidak ada.",
                    full: true,
                },
            ],
        },

        tabungan: {
            title: "Zakat Tabungan",
            percent: 2.5,
            nisab: "Nishab zakat penghasilan 2024: Rp6.859.394/bulan (setara 85 gram emas/tahun).",
            note: "Zakat tabungan dihitung dari total simpanan bersih.",
            law: "Zakat tabungan dikenakan apabila simpanan telah mencapai nisab dan memenuhi ketentuan haul.",
            fields: [
                {
                    label: "Saldo Tabungan",
                    name: "tabungan",
                    placeholder: "0",
                    type: "money",
                    role: "asset",
                    help: "Total tabungan yang dimiliki.",
                },
                {
                    label: "Deposito / Investasi",
                    name: "deposito",
                    placeholder: "0",
                    type: "money",
                    role: "asset",
                    help: "Isi 0 jika tidak ada.",
                },
                {
                    label: "Hutang Jatuh Tempo",
                    name: "hutang",
                    placeholder: "0",
                    type: "money",
                    role: "debt",
                    help: "Isi 0 jika tidak ada.",
                    full: true,
                },
            ],
        },

        perdagangan: {
            title: "Zakat Perniagaan",
            percent: 2.5,
            nisab: "Nishab zakat penghasilan 2024: Rp6.859.394/bulan (setara 85 gram emas/tahun).",
            note: "Zakat perniagaan dihitung dari aset usaha bersih.",
            law: "Zakat perniagaan dihitung dari harta usaha bersih seperti kas, stok barang, dan piutang lancar setelah dikurangi hutang.",
            fields: [
                {
                    label: "Kas / Saldo Usaha",
                    name: "kas_usaha",
                    placeholder: "0",
                    type: "money",
                    role: "asset",
                    help: "Uang kas atau saldo usaha.",
                },
                {
                    label: "Nilai Stok Barang",
                    name: "stok_barang",
                    placeholder: "0",
                    type: "money",
                    role: "asset",
                    help: "Nilai barang dagangan.",
                },
                {
                    label: "Piutang Lancar",
                    name: "piutang",
                    placeholder: "0",
                    type: "money",
                    role: "asset",
                    help: "Piutang yang masih mungkin ditagih.",
                },
                {
                    label: "Hutang Usaha",
                    name: "hutang",
                    placeholder: "0",
                    type: "money",
                    role: "debt",
                    help: "Hutang usaha jatuh tempo.",
                },
            ],
        },
    };

    const form = document.getElementById("zakatForm");
    const tabs = document.querySelectorAll(".zakat-tab");
    const jenisInput = document.getElementById("jenis");
    const formTitle = document.getElementById("formTitle");
    const nisabInfo = document.getElementById("nisabInfo");
    const formFields = document.getElementById("formFields");

    const liveZakatAmount = document.getElementById("liveZakatAmount");
    const liveZakatNote = document.getElementById("liveZakatNote");

    const resultCard = document.getElementById("zakatResultCard");
    const resultEmpty = document.getElementById("zakatResultEmpty");
    const resultContent = document.getElementById("zakatResultContent");
    const recommendationCard = document.getElementById(
        "zakatRecommendationCard",
    );

    const resultType = document.getElementById("resultType");
    const resultPercent = document.getElementById("resultPercent");
    const resultAmount = document.getElementById("resultAmount");
    const resultStatus = document.getElementById("resultStatus");
    const resultBase = document.getElementById("resultBase");
    const resultHarta = document.getElementById("resultHarta");
    const resultHutang = document.getElementById("resultHutang");
    const resultBersih = document.getElementById("resultBersih");
    const resultFinal = document.getElementById("resultFinal");
    const resultLaw = document.getElementById("resultLaw");

    let currentType = window.selectedZakat || "penghasilan";

    function onlyNumber(value) {
        return String(value || "").replace(/[^\d]/g, "");
    }

    function toNumber(value) {
        const clean = onlyNumber(value);

        return clean ? Number(clean) : 0;
    }

    function formatNumber(value) {
        return new Intl.NumberFormat("id-ID").format(value || 0);
    }

    function formatRupiah(value) {
        return "Rp" + formatNumber(Math.round(value || 0));
    }

    function formatMoneyInput(input) {
        const clean = onlyNumber(input.value);

        input.value = clean ? formatNumber(Number(clean)) : "";
    }

    function createField(field) {
        const wrapper = document.createElement("div");

        wrapper.className = field.full
            ? "zakat-field zakat-field-full"
            : "zakat-field";

        const label = document.createElement("label");

        label.setAttribute("for", field.name);
        label.textContent = field.label;

        const inputWrap = document.createElement("div");

        inputWrap.className =
            field.type === "money"
                ? "zakat-input-wrap has-prefix"
                : "zakat-input-wrap";

        if (field.type === "money") {
            const prefix = document.createElement("span");

            prefix.className = "zakat-input-prefix";
            prefix.textContent = "Rp";

            inputWrap.appendChild(prefix);
        }

        const input = document.createElement("input");

        input.id = field.name;
        input.name = field.name;

        /*
            Jangan pakai type="number".
            Kalau type number, browser tidak bisa menampilkan titik ribuan.
        */
        input.type = "text";
        input.inputMode = "numeric";
        input.autocomplete = "off";

        input.placeholder = field.placeholder || "0";
        input.dataset.role = field.role || "asset";
        input.dataset.type = field.type || "money";

        input.addEventListener("input", function () {
            if (field.type === "money") {
                formatMoneyInput(input);
            } else {
                input.value = onlyNumber(input.value);
            }

            updateLivePreview();
        });

        inputWrap.appendChild(input);

        const help = document.createElement("small");

        help.textContent = field.help || "";

        wrapper.appendChild(label);
        wrapper.appendChild(inputWrap);
        wrapper.appendChild(help);

        return wrapper;
    }

    function getCalculation() {
        const config = zakatData[currentType];

        let totalHarta = 0;
        let totalHutang = 0;

        if (currentType === "emas") {
            const berat = toNumber(
                formFields.querySelector('[data-role="gold_weight"]')?.value,
            );

            const hutang = toNumber(
                formFields.querySelector('[data-role="debt"]')?.value,
            );

            totalHarta = berat * HARGA_EMAS;
            totalHutang = hutang;
        } else {
            formFields.querySelectorAll("input").forEach((input) => {
                const value = toNumber(input.value);

                if (input.dataset.role === "debt") {
                    totalHutang += value;
                } else {
                    totalHarta += value;
                }
            });
        }

        const hartaBersih = Math.max(totalHarta - totalHutang, 0);

        let nisab = NISAB_TAHUNAN;

        if (currentType === "penghasilan") {
            nisab = NISAB_BULANAN;
        }

        const wajib = hartaBersih >= nisab;

        const zakat = wajib ? hartaBersih * (config.percent / 100) : 0;

        return {
            totalHarta,
            totalHutang,
            hartaBersih,
            zakat,
            wajib,
            nisab,
            percent: config.percent,
        };
    }

    function updateLivePreview() {
        const config = zakatData[currentType];
        const result = getCalculation();

        if (liveZakatAmount) {
            liveZakatAmount.textContent = formatRupiah(result.zakat);
        }

        if (!liveZakatNote) return;

        if (result.hartaBersih <= 0) {
            liveZakatNote.textContent =
                "Masukkan nominal harta untuk melihat estimasi zakat.";
            return;
        }

        if (result.wajib) {
            liveZakatNote.textContent =
                "✅ Harta telah mencapai nisab. Estimasi zakat sebesar " +
                formatRupiah(result.zakat);
        } else {
            liveZakatNote.textContent =
                "💙 Harta belum mencapai nisab sehingga belum wajib zakat. Namun Anda tetap dapat bersedekah.";
        }
    }

    function showResult() {
        const config = zakatData[currentType];
        const result = getCalculation();

        if (!resultCard || !resultEmpty || !resultContent) return;

        resultCard.classList.remove("is-empty");
        resultEmpty.hidden = true;
        resultContent.hidden = false;

        if (recommendationCard) {
            recommendationCard.hidden = false;
        }

        if (resultType) {
            resultType.textContent = config.title;
        }

        if (resultPercent) {
            resultPercent.textContent = config.percent + "%";
        }

        if (resultAmount) {
            resultAmount.textContent = formatRupiah(result.zakat);
        }

        if (resultStatus) {
            if (result.wajib) {
                resultStatus.innerHTML = `
            <span class="badge-success">
                ✅ Wajib Zakat
            </span>
        `;
            } else {
                resultStatus.innerHTML = `
            <span class="badge-warning">
                💙 Belum Wajib Zakat
            </span>
            <p style="margin-top:8px">
                Harta Anda belum mencapai nisab.
                Anda belum berkewajiban membayar zakat,
                namun tetap dianjurkan untuk bersedekah.
            </p>
        `;
            }
        }

        if (resultBase) {
            resultBase.textContent =
                "Dari dasar perhitungan " + formatRupiah(result.hartaBersih);
        }

        if (resultHarta) {
            resultHarta.textContent = formatRupiah(result.totalHarta);
        }

        if (resultHutang) {
            resultHutang.textContent = "-" + formatRupiah(result.totalHutang);
        }

        if (resultBersih) {
            resultBersih.textContent = formatRupiah(result.hartaBersih);
        }

        if (resultFinal) {
            resultFinal.textContent = formatRupiah(result.zakat);
        }

        if (resultLaw) {
            resultLaw.textContent = config.law;
        }

        resultCard.scrollIntoView({
            behavior: "smooth",
            block: "nearest",
        });
    }

    function setActiveType(type) {
        currentType = zakatData[type] ? type : "penghasilan";

        if (jenisInput) {
            jenisInput.value = currentType;
        }

        if (formTitle) {
            formTitle.textContent = zakatData[currentType].title;
        }

        if (nisabInfo) {
            nisabInfo.textContent = zakatData[currentType].nisab;
        }

        tabs.forEach(function (tab) {
            tab.classList.toggle("active", tab.dataset.zakat === currentType);
        });

        if (formFields) {
            formFields.innerHTML = "";

            zakatData[currentType].fields.forEach(function (field) {
                formFields.appendChild(createField(field));
            });
        }

        if (resultCard && resultEmpty && resultContent) {
            resultCard.classList.add("is-empty");
            resultEmpty.hidden = false;
            resultContent.hidden = true;
        }

        if (recommendationCard) {
            recommendationCard.hidden = true;
        }

        updateLivePreview();
    }

    tabs.forEach(function (tab) {
        tab.addEventListener("click", function () {
            setActiveType(tab.dataset.zakat);
        });
    });

    if (form) {
        form.addEventListener("submit", function (event) {
            event.preventDefault();
            showResult();
        });
    }

    const printButton = document.querySelector("[data-print-zakat]");

    if (printButton) {
        printButton.addEventListener("click", function () {
            window.print();
        });
    }

    window.selectZakat = function (type) {
        setActiveType(type);
    };

    setActiveType(currentType);
});

const checkboxLainnya = document.getElementById("lainnya");
const textareaLainnya = document.getElementById("lainnyaText");

checkboxLainnya.addEventListener("change", function () {
    textareaLainnya.style.display = this.checked ? "block" : "none";
});

function gabungkanAlasan() {
    let alasan = [];

    document.querySelectorAll(".alasan-check").forEach(function (item) {
        if (item.checked) {
            alasan.push(item.value);
        }
    });

    if (checkboxLainnya.checked) {
        let tambahan = textareaLainnya.value.trim();

        if (tambahan !== "") {
            alasan.push(tambahan);
        }
    }

    document.getElementById("catatan_verifikasi").value = alasan.length
        ? "• " + alasan.join("\n• ")
        : "";
}
