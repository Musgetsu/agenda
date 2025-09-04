document.addEventListener("DOMContentLoaded", () => {
    const agendaContainer = document.querySelector("#agenda-container");

    // ---- Chargement d’un mois ----
    function loadMonth(year, month, direction = 'left') {
        const url = `/agenda/view?year=${year}&month=${month}&view=month`;

        const oldTable = agendaContainer.querySelector(".calendar-table-wrapper");
        const currentHeight = oldTable ? oldTable.offsetHeight : 0;
        if (oldTable) {
            agendaContainer.style.height = currentHeight + "px";
            oldTable.style.position = "absolute";
        }

        fetch(url, {
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
        .then(response => response.json())
        .then(data => {
            // Nouveau wrapper
            const newWrapper = document.createElement('div');
            newWrapper.className = 'calendar-table-wrapper';
            newWrapper.innerHTML = data.calendarHtml;

            // Position initiale hors écran selon direction
            if (direction === 'left') {
                newWrapper.classList.add('slide-left-in');
            } else {
                newWrapper.classList.add('slide-right-in');
            }

            agendaContainer.appendChild(newWrapper);

            // Forcer reflow pour que l'animation s'applique
            newWrapper.getBoundingClientRect();

            // Lancer l’animation
            newWrapper.classList.remove('slide-left-in', 'slide-right-in');
            newWrapper.classList.add('slide-in');

            // Sortie de l’ancien
            if (oldTable) {
                oldTable.classList.remove('slide-in');
                oldTable.classList.add(direction === 'left' ? 'slide-left-out' : 'slide-right-out');

                // Supprimer après animation
                setTimeout(() => {
                    oldTable.remove();
                    agendaContainer.style.height = "auto"; // revenir en responsive
                }, 500);
            }

            // Mettre à jour le header et les flèches
            document.querySelector("#agenda-month").textContent = data.monthName + " " + data.yearNumber;
            document.querySelector("#prev-month").dataset.year = data.prevYear;
            document.querySelector("#prev-month").dataset.month = data.prevMonth;
            document.querySelector("#next-month").dataset.year = data.nextYear;
            document.querySelector("#next-month").dataset.month = data.nextMonth;
        })
        .catch(err => console.error("Erreur lors du chargement AJAX:", err));
    }

    // ---- Chargement d’un jour (slots) ----
    function loadDay(year, month, day) {
        const url = `/agenda/view?year=${year}&month=${month}&day=${day}&view=day`;

        fetch(url, {
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
        .then(response => response.json())
        .then(data => {
            let section = document.getElementById("day-slots-section");
            if (!section) {
                section = document.createElement("div");
                section.id = "day-slots-section";
                document.querySelector(".calendar").after(section);
            }
            section.innerHTML = data.slotsHtml;
        })
        .catch(err => console.error("Erreur lors du chargement des slots:", err));
    }

    // ---- Gestion des clics ----
    document.addEventListener("click", (e) => {
        // Navigation mois
        if (e.target.matches("#prev-month, #next-month")) {
            e.preventDefault();
            const year = e.target.dataset.year;
            const month = e.target.dataset.month;
            const direction = e.target.id === 'next-month' ? 'left' : 'right';
            loadMonth(year, month, direction);
        }

        // Clic sur un jour
        const td = e.target.closest(".day-cell");
        if (td) {
            const year = td.dataset.year;
            const month = td.dataset.month;
            const day = td.dataset.day;
            loadDay(year, month, day);
        }
    });
});
