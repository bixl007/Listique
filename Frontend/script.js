// Register GSAP plugins
gsap.registerPlugin(ScrollTrigger);

// Loader robotic percentage simulation
window.addEventListener("load", () => {
  const loaderText = document.getElementById("loader-text");
  const loader = document.getElementById("loader");
  const video = document.getElementById("bg-video");
  let percent = 0;

  // Increment percentage every 50ms
  const interval = setInterval(() => {
    if (percent < 100) {
      percent += 2; // Adjust speed by changing increment value
      loaderText.textContent = `STARTING UP ${percent}%`;
    }
  }, 50);

  // Ensure the loader hides after 3 seconds, regardless of video load status
  setTimeout(() => {
    clearInterval(interval); // Stop the interval if still running
    loaderText.textContent = "STARTING UP 100%";

    // Fade out the loader
    gsap.to(loader, {
      duration: 0.8,
      opacity: 0,
      y: -50,
      ease: "power2.out",
      onComplete: () => {
        loader.style.display = "none"; // Hide the loader after animation

        // Animate hero section text after loader hides
        gsap.from("#home h1, #home p, #home a", {
          duration: 1,
          y: 50,
          opacity: 0,
          stagger: 0.2,
          ease: "back.out(1.7)"
        });
      }
    });
  }, 3000); // Minimum loader duration of 3 seconds
});

// Scroll-triggered section animations
document.querySelectorAll("section").forEach(section => {
  gsap.from(section, {
    scrollTrigger: {
      trigger: section,
      start: "top 85%",
      toggleActions: "play none none reverse"
    },
    opacity: 0,
    y: 30,
    duration: 1,
    ease: "power2.out"
  });
});

// Stagger animations for grids
gsap.utils.toArray("#features .grid > div, #services .grid > div, #portfolio div").forEach(item => {
  gsap.from(item, {
    scrollTrigger: {
      trigger: item,
      start: "top 90%",
      toggleActions: "play none none none"
    },
    opacity: 0,
    y: 20,
    duration: 0.8,
    ease: "power2.out"
  });
});

// Custom Cursor Animation
const cursor = document.getElementById("custom-cursor");

document.addEventListener("mousemove", e => {
  gsap.to(cursor, {
    x: e.clientX,
    y: e.clientY,
    duration: 0.1,
    ease: "power2.out"
  });
});

document.querySelectorAll("a, button").forEach(elem => {
  elem.addEventListener("mouseenter", () => {
    gsap.to(cursor, { scale: 1.4, duration: 0.2, ease: "power2.out" });
  });
  elem.addEventListener("mouseleave", () => {
    gsap.to(cursor, { scale: 1, duration: 0.2, ease: "power2.out" });
  });
});
