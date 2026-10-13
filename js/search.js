/*document.addEventListener("DOMContentLoaded", async () => {
    const searchBar = document.getElementById("searchBar");
    if(!searchBar) return;

    const page = document.body.dataset.searchPage;
    let allItems = [];

    try {
        const response = await fetch(`item/fulltext_service/${page}`);
        allItems = await response.json();
    } catch (error) {
        console.error("Failed to load items", error);
        return;
    }
    searchBar.style.display = "";

    const searchInput = document.getElementById("searchInput");

    searchInput.addEventListener("input", () => {
        const word = searchInput.value.trim().toLowerCase();
        let countFoundItems = 0;

        //on cherche tous les elements dans la page html qui correspondent au nom item-card
        //listener sur chaque keystroke
        document.querySelectorAll(".item-card").forEach(li => {
            const itemId = li.dataset.itemId;

            //pour chaque li item-card, on cherche l'element correspondant dans allItems en filtrant par id
            const itemInfo = allItems.find(item => item.id == itemId);

            //concatenation des infos qui nous intéressent, on en fait un seul string
            const toSearch = (itemInfo.title + " " + itemInfo.description + " " + itemInfo.creator).toLowerCase();


            //si l'input entré dans la barre de recherche fait partie du string toSearch,
            // on affiche item-card, sinon on affiche rien
            if (toSearch.includes(word)){
                li.style.display = "";
                countFoundItems++;
            }else{
                li.style.display = "none";
            }
        });

        //on regarde si dans chaque section il y a un item ou pas, sinon on n'affiche plus le titre avec une section vide
        //on récupère chaque section et dans chaque section on regarde s'il y a des items/li visibles
        document.querySelectorAll(".item-section").forEach(section => {
            let visibleInSection = 0;
            section.querySelectorAll(".item-card").forEach(card => {
                if(card.style.display !== "none"){
                    visibleInSection++;
                }
            });
            if(visibleInSection === 0) section.style.display="none";
            else section.style.display="";

        })

        const noItemFound = document.getElementById("noItemFound");
        if(countFoundItems === 0) noItemFound.style.display ="";
        else noItemFound.style.display ="none";
    });

    //on déclenche un event 'input' s'il y a une valeur dans searchInput qui n'est pas déjà déclenché par un keystroke, càd
    //quand on reload la page et on veut lancer la recherche
    if (searchInput.value) {
        searchInput.dispatchEvent(new Event("input"));
    }





})*/
document.addEventListener("DOMContentLoaded", () => {
    const searchBar = document.getElementById("searchBar");
    if (!searchBar) return;

    const dropdown = document.getElementById("cat-filter");
    if (!dropdown) return;

    dropdown.style.display = "";

    const page = document.body.dataset.searchPage;
    const searchInput = document.getElementById("searchInput");

    searchBar.style.display = "";

    // déclenche sur chaque keystroke
    searchInput.addEventListener("input", async () => {
        const pattern = searchInput.value.trim();
        const filter = dropdown.value ?? 0;
        await runSearch(page, pattern, filter);
    });

    dropdown.addEventListener("change", async () => {
        const pattern = searchInput.value.trim() ?? "";
        const filter = parseInt(dropdown.value);
        await runSearch(page, pattern, filter);
    })
    //on déclenche un event 'input' s'il y a une valeur dans searchInput qui n'est pas déjà déclenché par un keystroke, càd
    //quand on reload la page et on veut lancer la recherche
    if (searchInput.value || dropdown.value) {
        searchInput.dispatchEvent(new Event("input"));
    }
});

async function runSearch(page, pattern, filter) {
    try {
        // construit l'url pour lancer le service seulement si le pattern n'est pas vide
        pattern = pattern ?? "";
        filter = filter ?? 0;
        const url = `item/fulltext_service/${page}/${encodeURIComponent(encodeURIComponent(pattern))}/${encodeURIComponent(filter)}`;

        //lancer le service, requête au serveur
        const response = await fetch(url);
        const data = await response.json();


        //met à jour les items à afficher
        updateItemsList(data.items);

        //met à jour les liens
        updateItemsLinks(data.encoded_state);

    } catch (error) {
        console.error("Search failed", error);
    }
}

function updateItemsList(items) {
    const cards = document.querySelectorAll(".item-card");

    //récupère les id des items dans le résultat de recherche
    const matchingIds = new Set();
    for (const item of items) {
        matchingIds.add(String(item.id));
    }

    //pour chaque item dans la vue on regarde si son id est dans la réponse, si oui on l'affiche
    let visibleCount = 0;
    cards.forEach(card => {
        const id = card.dataset.itemId;
        if (matchingIds.has(id)) {
            card.style.display = "";
            visibleCount++;
        } else {
            card.style.display = "none";
        }
    });

    // si une rubrique est vide après recherche, on affiche pas son titre
    document.querySelectorAll(".item-section").forEach(section => {
        let visibleInSection = 0;
        section.querySelectorAll(".item-card").forEach(card => {
            if(card.style.display !== "none"){
                visibleInSection++;
            }
        });
        if(visibleInSection === 0) section.style.display="none";
        else section.style.display="";

    })

    const noItemFound = document.getElementById("noItemFound");
    if (noItemFound) {
        noItemFound.style.display = visibleCount === 0 ? "" : "none";
    }
}

function updateItemsLinks(encodedState) {
    document.querySelectorAll(".item-card .item-card-link").forEach( link => {
        const baseUrl = link.dataset.baseUrl;

        if (encodedState) {
            link.href = `${baseUrl}/${encodedState}`;
        } else {
            link.href = baseUrl;
        }
    })
}