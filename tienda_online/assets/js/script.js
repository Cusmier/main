// --- Navegación ---
const enlaces = document.querySelectorAll('nav a');
const secciones = document.querySelectorAll('section.seccion');

function mostrarSeccion(id) {
  secciones.forEach(s => s.classList.remove('active'));
  document.getElementById(id).classList.add('active');
  enlaces.forEach(e => e.classList.remove('active-link'));
  document.querySelector(`nav a[data-section="${id}"]`)?.classList.add('active-link');
}

enlaces.forEach(e => e.addEventListener('click', ev => {
  ev.preventDefault();
  mostrarSeccion(e.getAttribute('data-section'));
}));

// --- Productos base ---
const productos = [
  { nombre: 'Camiseta Cool', precio: 25, categoria: 'Moda', img: 'https://picsum.photos/id/1018/400/300' },
  { nombre: 'Auriculares Gaming', precio: 99, categoria: 'Gaming', img: 'https://picsum.photos/id/1015/400/300' },
  { nombre: 'Lámpara Moderna', precio: 45, categoria: 'Hogar', img: 'https://picsum.photos/id/1011/400/300' },
  { nombre: 'Smartphone 2025', precio: 799, categoria: 'Tecnología', img: 'https://picsum.photos/id/1005/400/300' }
];

// --- Productos subidos por usuario ---
let productosSubidos = JSON.parse(localStorage.getItem('productosSubidos')) || [];
productosSubidos.forEach(p => productos.push(p)); // agregar al array principal

// --- Slider ---
function mostrarSliderDestacados(lista) {
  const slider = document.getElementById('slider-productos');
  if (!slider) return;

  slider.innerHTML = '';
  const productosDuplicados = [...lista, ...lista];

  productosDuplicados.forEach(p => {
    const div = document.createElement('div');
    div.className = 'producto';
    div.innerHTML = `
      <img src="${p.img}" alt="${p.nombre}">
      <h3>${p.nombre}</h3>
      <p>$${p.precio}</p>
      <button onclick='agregarCarrito(${JSON.stringify(p)})'>Agregar</button>
    `;
    slider.appendChild(div);
  });

  slider.style.display = 'flex';
  slider.style.overflow = 'hidden';
  slider.style.width = '100%';
  slider.style.scrollBehavior = 'auto';
  slider.style.gap = '20px';
  slider.style.whiteSpace = 'nowrap';
}

function iniciarSliderContinuo() {
  const slider = document.getElementById('slider-productos');
  if (!slider) return;

  let pos = 0;
  const velocidad = 1;

  function mover() {
    pos += velocidad;
    const maxScroll = slider.scrollWidth / 2;
    if (pos >= maxScroll) pos = 0;
    slider.scrollLeft = pos;
    requestAnimationFrame(mover);
  }

  mover();
}

// Mostrar slider inicial
const destacados = productos.slice(0, 4);
mostrarSliderDestacados(destacados);
iniciarSliderContinuo();

// --- Cargar productos desde la API PHP ---
async function cargarProductosDesdeAPI(url, page = 1, limit = 10) {
  try {
    const response = await fetch(`${url}?page=${page}&limit=${limit}`);
    if (!response.ok) throw new Error('Error al cargar productos desde API');
    const res = await response.json();

    if (res.status === 'success') {
      res.data.forEach(p => {
        productos.push({
          nombre: p.nombre,
          precio: parseFloat(p.precio),
          categoria: p.categoria || 'General',
          img: p.imagen_url || 'https://via.placeholder.com/400x300'
        });
      });

      mostrarProductos(productos, 'lista-productos');
      mostrarProductos(productos, 'lista-productos-inicio');

      const destacadosAPI = productos.slice(0, 4);
      mostrarSliderDestacados(destacadosAPI);
      iniciarSliderContinuo();
    } else {
      console.warn('API dijo:', res.message);
    }
  } catch (err) {
    console.error(err);
  }
}

// Cargar productos desde tu API
cargarProductosDesdeAPI('controllers/obtener_productos.php');

// --- Mostrar productos ---
function mostrarProductos(lista, id) {
  const contenedor = document.getElementById(id);
  if (!contenedor) return;

  contenedor.innerHTML = '';
  lista.forEach(p => {
    const div = document.createElement('div');
    div.className = 'producto';
    div.innerHTML = `
      <img src="${p.img}" alt="${p.nombre}">
      <h3>${p.nombre}</h3>
      <p>$${p.precio}</p>
      <button onclick='agregarCarrito(${JSON.stringify(p)})'>Agregar</button>`;
    contenedor.appendChild(div);
  });
}

// Mostrar productos iniciales
mostrarProductos(productos, 'lista-productos');
mostrarProductos(productos, 'lista-productos-inicio');
mostrarProductos(productosSubidos, 'lista-productos-nuevos');

// --- Carrito ---
let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
function guardarCarrito() { localStorage.setItem('carrito', JSON.stringify(carrito)); }

function actualizarCarrito() {
  const items = document.getElementById('items-carrito');
  const totalItems = document.getElementById('total-items');
  const totalCarrito = document.getElementById('total-carrito');
  if (!items) return;

  items.innerHTML = '';
  let total = 0;

  carrito.forEach((p, i) => {
    const div = document.createElement('div');
    div.innerHTML = `
      ${p.nombre} x ${p.cantidad} - $${p.precio * p.cantidad}
      <button onclick="cambiarCantidad(${i},-1)">-</button>
      <button onclick="cambiarCantidad(${i},1)">+</button>
      <button onclick="removerProducto(${i})">🗑️</button>`;
    items.appendChild(div);
    total += p.precio * p.cantidad;
  });

  totalItems.textContent = carrito.reduce((a,b)=>a+b.cantidad,0);
  totalCarrito.textContent = total.toFixed(2);
  guardarCarrito();
}

function agregarCarrito(p) {
  const i = carrito.findIndex(x => x.nombre === p.nombre);
  if (i >= 0) carrito[i].cantidad++;
  else carrito.push({...p, cantidad:1});
  actualizarCarrito();
  mostrarNotificacion(`✅ ${p.nombre} agregado al carrito`);
}

function cambiarCantidad(i,d){
  carrito[i].cantidad+=d;
  if(carrito[i].cantidad<1) carrito[i].cantidad=1;
  actualizarCarrito();
}

function removerProducto(i){
  carrito.splice(i,1);
  actualizarCarrito();
}

actualizarCarrito();

// --- Resto del código (pago, buscador, subir producto, notificación, menú) sigue igual ---
