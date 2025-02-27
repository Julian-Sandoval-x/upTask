(function () {
  obtenerTareas();
  let tareas = [];
  let filtradas = [];

  // Boton para mostra el modal del agregar tareas
  const nuevaTareaBtn = document.querySelector("#agregar-tarea");
  nuevaTareaBtn.addEventListener("click", function () {
    mostrarFormulario();
  });

  // filtros de busqueda
  const filtros = document.querySelectorAll("#filtros input[type='radio']");
  filtros.forEach((radio) => {
    radio.addEventListener("input", filtrarTareas);
  });

  function filtrarTareas(e) {
    const filtro = e.target.value;

    if (filtro !== "") {
      filtradas = tareas.filter((tarea) => tarea.estado === filtro);
    } else {
      filtradas = [];
    }
    mostrarTareas();

    filtradas = [];
  }

  async function obtenerTareas() {
    try {
      const id = obtenerProyecto();
      const url = `/api/tareas?id=${id}`;
      const respuesta = await fetch(url);
      const resultado = await respuesta.json();
      tareas = resultado.tareas;
      mostrarTareas();
    } catch (error) {
      console.log(error);
    }
  }

  function mostrarTareas() {
    limpiarTareas();
    totalCompletas();
    totalPendientes();

    const arrayTareas = filtradas.length ? filtradas : tareas;

    if (arrayTareas.length === 0) {
      const contenedorTareas = document.querySelector("#listado-tareas");
      const textoNoTareas = document.createElement("LI");
      textoNoTareas.textContent = "No hay tareas en este proyecto";
      textoNoTareas.classList.add("no-tareas");
      contenedorTareas.appendChild(textoNoTareas);
      return;
    }

    // Diccionario de estados
    const estados = { 0: "Pendiente", 1: "Completa" };

    arrayTareas.forEach((tarea) => {
      const contenedorTarea = document.createElement("LI");
      contenedorTarea.dataset.tareaId = tarea.id;
      contenedorTarea.classList.add("tarea");

      const nombreTarea = document.createElement("P");
      nombreTarea.textContent = tarea.nombre;
      nombreTarea.ondblclick = function () {
        mostrarFormulario(true, { ...tarea });
      };

      const opcionesDiv = document.createElement("DIV");
      opcionesDiv.classList.add("opciones");

      // Botones
      const btnEstadoTarea = document.createElement("BUTTON");
      btnEstadoTarea.classList.add("estado-tarea");
      btnEstadoTarea.classList.add(`${estados[tarea.estado].toLowerCase()}`);
      btnEstadoTarea.textContent = estados[tarea.estado];
      btnEstadoTarea.dataset.estadoTarea = tarea.estado;
      btnEstadoTarea.ondblclick = function () {
        cambiarEstadoTarea({ ...tarea });
      };

      const btnEliminarTarea = document.createElement("BUTTON");
      btnEliminarTarea.classList.add("eliminar-tarea");
      btnEliminarTarea.dataset.idTarea = tarea.id;
      btnEliminarTarea.textContent = "Eliminar";
      btnEliminarTarea.ondblclick = function () {
        confirmarEliminarTarea({ ...tarea });
      };

      opcionesDiv.appendChild(btnEstadoTarea);
      opcionesDiv.appendChild(btnEliminarTarea);
      contenedorTarea.appendChild(nombreTarea);
      contenedorTarea.appendChild(opcionesDiv);

      const listadoTareas = document.querySelector("#listado-tareas");
      listadoTareas.appendChild(contenedorTarea);
    });
  }

  function totalPendientes() {
    const totalPendientes = tareas.filter((tarea) => tarea.estado === "0");
    const pendientesRadio = document.querySelector("#pendientes");

    if (totalPendientes.length === 0) {
      pendientesRadio.disabled = true;
    } else {
      pendientesRadio.disabled = false;
    }
  }

  function totalCompletas() {
    const totalCompletas = tareas.filter((tarea) => tarea.estado === "1");
    const completasRadio = document.querySelector("#completadas");

    if (totalCompletas.length === 0) {
      completasRadio.disabled = true;
    } else {
      completasRadio.disabled = false;
    }
  }

  function mostrarFormulario(editar = false, tarea = {}) {
    const modal = document.createElement("DIV");
    modal.classList.add("modal");
    modal.innerHTML = `
      <form class="formulario nueva-tarea">
      <legend>${editar ? "Editar Tarea" : "Añade una Nueva Tarea"}</legend>
        <div class="campo">
          <label> Tarea</label>
          <input
            type="text"
            name="tarea"
            placeholder=" ${
              tarea.nombre
                ? "Edita la Tarea"
                : "Añadir Tarea al Proyecto Actual"
            }"
            id="tarea"
            value="${tarea.nombre ? tarea.nombre : ""}"
          />
        </div>
        <div class="opciones">
          <input
            type="submit"
            class="submit-nueva-tarea"
            value="${tarea.nombre ? "Guardar Cambios" : "Añadir Tarea"}"
          />
          <button type="button" class="cerrar-modal">
            Cancelar
          </button>
        </div>
      </form>
      `;

    setTimeout(() => {
      const formulario = document.querySelector(".formulario");
      formulario.classList.add("animar");
    }, 0);

    modal.addEventListener("click", function (e) {
      e.preventDefault();
      if (e.target.classList.contains("cerrar-modal")) {
        const formulario = document.querySelector(".formulario");
        formulario.classList.add("cerrar");
        setTimeout(() => {
          modal.remove();
        }, 500);
      }

      if (e.target.classList.contains("submit-nueva-tarea")) {
        const nombreTarea = document.querySelector("#tarea").value.trim();
        if (nombreTarea === "") {
          // Mostrar una alerta de error
          mostrarAlerta(
            "El Nombre de la Tarea es Obligatorio",
            "error",
            document.querySelector(".formulario legend")
          );
          return; // Detenemos la ejecución
        }

        if (editar) {
          tarea.nombre = nombreTarea;
          actualizarTarea(tarea);
        } else {
          agregarTarea(nombreTarea);
        }
      }
    });

    document.querySelector(".dashboard").appendChild(modal);
  }
  // Muestra un mensaje en la interfaz
  function mostrarAlerta(mensaje, tipo, referencia) {
    // Previene la creacion de multiples alertas
    const alertaPrevia = document.querySelector(".alerta");
    if (alertaPrevia) {
      alertaPrevia.remove();
    }
    const alerta = document.createElement("DIV");
    alerta.classList.add("alerta", tipo);
    alerta.textContent = mensaje;

    // Inserta la alerta antes del legend
    referencia.parentElement.insertBefore(alerta, referencia.nextSibling);

    // Eliminar la alerta despues de 5 segundos
    setTimeout(() => {
      alerta.remove();
    }, 5000);
  }

  // Consultar el servidor para añadir una nueva tarea al proyecto actual
  async function agregarTarea(tarea) {
    // Construimos la petición
    const datos = new FormData();
    datos.append("nombre", tarea);
    datos.append("proyectoId", obtenerProyecto());

    try {
      const url = "http://localhost:3000/api/tarea";
      const respuesta = await fetch(url, {
        method: "POST",
        body: datos,
      });

      const resultado = await respuesta.json();

      mostrarAlerta(
        resultado.mensaje,
        resultado.tipo,
        document.querySelector(".formulario legend")
      );

      // Si la tarea se agrego correctamente
      if (resultado.tipo === "exito") {
        const modal = document.querySelector(".modal");
        setTimeout(() => {
          modal.remove();
        }, 3000);

        // Agregar el objeto de tareas al global de tareas
        const taresObj = {
          id: String(resultado.id),
          nombre: tarea,
          estado: 0,
          proyectoId: resultado.proyectoId,
        };

        // Agregar la tarea al arreglo de tareas
        tareas = [...tareas, taresObj];

        // Limpiamos el HTML
        limpiarTareas();

        // Mostramos las tareas
        mostrarTareas();
      }
    } catch (error) {
      console.log(error);
    }
  }

  function cambiarEstadoTarea(tarea) {
    const nuevoEstado = tarea.estado === "1" ? "0" : "1";
    tarea.estado = nuevoEstado;
    actualizarTarea(tarea);
  }

  async function actualizarTarea(tarea) {
    const { estado, id, nombre, proyectoId } = tarea;

    const datos = new FormData();
    datos.append("id", id);
    datos.append("nombre", nombre);
    datos.append("estado", estado);
    datos.append("proyectoId", obtenerProyecto());

    try {
      const url = "http://localhost:3000/api/tarea/actualizar";

      const respuesta = await fetch(url, {
        method: "POST",
        body: datos,
      });

      const resultado = await respuesta.json();

      if (resultado.respuesta.tipo === "exito") {
        Swal.fire(
          resultado.respuesta.mensaje,
          resultado.respuesta.mensaje,
          "success"
        );

        const modal = document.querySelector(".modal");
        if (modal) {
          modal.remove();
        }

        // Actualizamos el VDOM
        tareas = tareas.map((tareaMemoria) => {
          if (tareaMemoria.id === id) {
            tareaMemoria.estado = estado;
            tareaMemoria.nombre = nombre;
          }

          return tareaMemoria;
        });

        mostrarTareas();
      }
    } catch (error) {
      console.log(error);
    }
  }

  function confirmarEliminarTarea(tarea) {
    Swal.fire({
      title: "¿Seguro que Quieres Eliminar la Tarea?",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
    }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {
        eliminarTarea(tarea);
      }
    });
  }

  async function eliminarTarea(tarea) {
    const { id, nombre, estado, proyectoId } = tarea;
    const datos = new FormData();

    datos.append("id", id);
    datos.append("nombre", nombre);
    datos.append("estado", estado);
    datos.append("proyectoId", obtenerProyecto());

    try {
      const url = "http://localhost:3000/api/tarea/eliminar";
      const respuesta = await fetch(url, {
        method: "POST",
        body: datos,
      });

      const resultado = await respuesta.json();

      if (resultado.resultado) {
        // mostrarAlerta(
        //   resultado.mensaje,
        //   resultado.tipo,
        //   document.querySelector(".contenedor-nueva-tarea")
        // );

        Swal.fire("Eliminado", resultado.mensaje, "success");

        tareas = tareas.filter((tareaMemoria) => tareaMemoria.id !== tarea.id);
        mostrarTareas();
      }
    } catch (error) {
      console.log(error);
    }
  }

  // Obtiene el id del proyecto actual
  function obtenerProyecto() {
    // Obtenemos la url actual
    const proyectoParams = new URLSearchParams(window.location.search);
    // Creamos un objeto a partir de los parametros de la url
    const proyecto = Object.fromEntries(proyectoParams.entries());
    // Retornamos el id del proyecto actual
    return proyecto.id;
  }

  function limpiarTareas() {
    const listadoTareas = document.querySelector("#listado-tareas");

    while (listadoTareas.firstChild) {
      listadoTareas.removeChild(listadoTareas.firstChild);
    }
  }
})();
