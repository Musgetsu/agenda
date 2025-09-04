
const themeToggle = document.getElementById('theme-toggle');
const toggleThumb = themeToggle.querySelector('.toggle-thumb');

function applyTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);
  localStorage.setItem('theme', theme);

  // Rotation
  if(toggleThumb.textContent = theme === 'dark')
  {
  toggleThumb.classList.add('rotatingPlus');
  }
  else
  {
  toggleThumb.classList.remove('rotatingPlus');
  }

  // Changer l'icône
  toggleThumb.textContent = theme === 'dark' ? '☀️' : '🌙';
}

// Thème initial
const savedTheme = localStorage.getItem('theme');
if (savedTheme) {
  applyTheme(savedTheme);
} else {
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  applyTheme(prefersDark ? 'dark' : 'light');
}

// Toggle au clic
themeToggle.addEventListener('click', () => {
  const current = document.documentElement.getAttribute('data-theme');
  applyTheme(current === 'dark' ? 'light' : 'dark');
});