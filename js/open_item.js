document.addEventListener("DOMContentLoaded", async () => {
    const carousel = document.getElementById("item-carousel");

    if (!carousel) {
        return;
    }

    const itemId = carousel.dataset.itemId;

    try {
        const response = await fetch(`item/carousel_service/${itemId}`);

        if (!response.ok) {
            const errorData = await response.json();
            console.error(`Carousel service error (${response.status}):`, errorData.error);
            return;
        }
        const data = await response.json();
        const images = data.images || [];

        if (images.length === 0) {
            return;
        }

        //le fallback = html qui doit fonctionner quand y a plus de js, ne doit pas être visible si js est actif

        document.getElementById("carousel-nojs").style.display = "none";
        document.getElementById("extra-images-nojs").style.display = "none";

        // conteneur le plus general du carousel
        const carouselDiv = document.createElement("div");
        carouselDiv.id = "itemCarousel";
        carouselDiv.className = "carousel slide";

        const inner = document.createElement("div");
        inner.className = "carousel-inner";
        carouselDiv.appendChild(inner);

        //pour chaque image dans la réponse, on crée un élément div qu'on nomme slide; ET on crée aussi un chemin qu'on lie au slide
        //active pour marquer l'image qui sera visible quand on ouvre la page
        images.forEach((imagePath, index) => {
            const slide = document.createElement("div");
            slide.className = "carousel-item";
            if (index === 0) {
                slide.classList.add("active");
            }

            const img = document.createElement("img");
            img.src = imagePath;
            img.className = "d-block w-100";

            slide.appendChild(img);
            inner.appendChild(slide);
        });


        const prevBtn = document.createElement("button");
        prevBtn.className = "carousel-control-prev";
        prevBtn.type = "button";
        prevBtn.dataset.bsTarget = "#itemCarousel";
        prevBtn.dataset.bsSlide = "prev";
        prevBtn.innerHTML = '<span class="carousel-control-prev-icon"></span>';
        carouselDiv.appendChild(prevBtn);

        const nextBtn = document.createElement("button");
        nextBtn.className = "carousel-control-next";
        nextBtn.type = "button";
        nextBtn.dataset.bsTarget = "#itemCarousel";
        nextBtn.dataset.bsSlide = "next";
        nextBtn.innerHTML = '<span class="carousel-control-next-icon"></span>';
        carouselDiv.appendChild(nextBtn);

        carousel.appendChild(carouselDiv);

    // Toujours activer bootstrap sur l'élément qui nous intéresse
        const bsCarousel = new bootstrap.Carousel(carouselDiv);

        const thumbsContainer = document.createElement("div");
        thumbsContainer.className = "carousel-miniatures";

        images.forEach((imagePath, index) => {
            const thumb = document.createElement("img");
            thumb.src = imagePath;
            thumb.className = "miniature";

            thumb.addEventListener("click", () => {
                bsCarousel.to(index);
            });

            thumbsContainer.appendChild(thumb);
        });
        carousel.appendChild(thumbsContainer);

    } catch (error) {
        console.error("Erreur dans le chargement du carousel:", error);
    }

});