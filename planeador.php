<?php
require_once 'config.php';
require_login();
require_role(['Estudiante','Profesor','admin']);

// --- Función colores por prioridad ---
function colorPorPrioridad($prioridad) {
    return match (strtolower($prioridad)) {
        'alta' => '#ffcdd2',
        'media' => '#fff9c4',
        'baja' => '#c8e6c9',
        default => '#e0e0e0',
    };
}

// --- Obtener tareas ---
$stmt = $pdo->query("
    SELECT t.*, u.username, u.name, u.cargo
    FROM tasks t
    LEFT JOIN users u ON t.assigned_to = u.id
    ORDER BY t.order_index ASC
");
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Agrupar por estado ---
$estados = ['todo'=>[],'doing'=>[],'review'=>[],'done'=>[]];
foreach($tareas as $t){
    $estado = $t['status'] ?? 'todo';
    if(isset($estados[$estado])){
        $estados[$estado][] = $t;
    }
}

// --- Función para obtener subtareas ---
function obtenerSubtareas($pdo, $task_id){
    $stmt = $pdo->prepare("SELECT * FROM subtasks WHERE task_id=? ORDER BY id ASC");
    $stmt->execute([$task_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- Función para obtener fecha en español---
function fechaEnEspanol($fecha) {
    if (!$fecha) return 'Sin definir';
    $meses = [
        'January'=>'enero','February'=>'febrero','March'=>'marzo','April'=>'abril',
        'May'=>'mayo','June'=>'junio','July'=>'julio','August'=>'agosto',
        'September'=>'septiembre','October'=>'octubre','November'=>'noviembre','December'=>'diciembre'
    ];
    $timestamp = strtotime($fecha);
    $mes = $meses[date('F', $timestamp)];
    return date('d', $timestamp) . ' ' . $mes . ' ' . date('Y', $timestamp);
}

// --- Función para obtener comentarios ---
function obtenerComentarios($pdo, $task_id){
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE task_id=? ORDER BY id ASC");
    $stmt->execute([$task_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Planeador Scrumban Profesional 🚀</title>
<style>
body { font-family:'Segoe UI',Arial,sans-serif; background:#f0f2f5; margin:0; }
header { background:#5563DE; color:#fff; padding:10px 20px; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:1000; }
header h1 { margin:0; font-size:22px; }

.userbar { background:rgba(255,255,255,0.2); padding:6px 12px; border-radius:8px; font-size:14px; }

.btn-crear, .btn-logout ,.btn-chatss{ border:none; padding:8px 14px; border-radius:6px; cursor:pointer; font-weight:bold; transition:0.2s; }
.btn-crear { background:#00c853; color:white; }    
.btn-crear:hover { background:#009624; }

.btn-logout { background:#e53935; color:white; }
.btn-logout:hover { background:#b71c1c; }

main { display:flex; justify-content:space-around; align-items:flex-start; padding:20px; min-height:80vh; gap:15px; overflow-x:auto; }
.column { background:white; border-radius:12px; width:23%; padding:15px; box-shadow:0 6px 12px rgba(0,0,0,0.1); min-height:70vh; transition:0.3s; }
.column h2 { text-align:center; color:#334; border-bottom:2px solid #5563DE; padding-bottom:5px; }

/* --- Ficha de tarea compacta --- */
.task { border-radius:10px; padding:6px 8px; margin:6px 0; cursor:grab; box-shadow:0 4px 6px rgba(0,0,0,0.1); transition:all 0.3s ease; position:relative; border-left:6px solid #5563DE; }
.task:hover { transform:scale(1.03); box-shadow:0 6px 10px rgba(0,0,0,0.15); }
.task strong { font-size:15px; display:block; margin-bottom:2px; }
.task p { margin:2px 0; font-size:13px; color:#333; }
.task small, .subtareas, .comentarios { display:block; font-size:12px; color:#444; line-height:1.2; margin-top:2px; }
.task button.delete-btn { position:absolute; top:5px; right:5px; background:#e53935; color:white; border:none; border-radius:4px; padding:2px 6px; cursor:pointer; font-size:12px; transition:0.2s; }
.task button.delete-btn:hover { background:#b71c1c; }
.acciones-tarea button { margin-left:3px; padding:2px 4px; font-size:11px; background:#5563DE; color:white; border:none; border-radius:4px; cursor:pointer; }

.progreso { background:#ddd; border-radius:5px; height:6px; margin:3px 0; }
.progreso > div { height:100%; border-radius:5px; transition:width 0.3s; }

footer { text-align:center; padding:10px; color:#555; font-size:14px; }

#notiBell { font-size:22px; position:relative; cursor:pointer; }
#notiCount { background:red; color:white; font-size:12px; padding:2px 6px; border-radius:50%; position:absolute; top:-8px; right:-10px; display:none; }
#notiBox { display:none; position:absolute; top:45px; right:0; background:white; color:#333; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.3); width:320px; max-height:400px; overflow-y:auto; z-index:10000; padding:10px; }
#notiBox::-webkit-scrollbar { width:6px; }
#notiBox::-webkit-scrollbar-thumb { background:#ccc; border-radius:10px; }

.alertaPopup { position: fixed; top: 20px; right: 20px; background: #ff5252; color: white; padding: 15px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.3); z-index: 9999; animation: fadeIn 0.5s; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
@keyframes temblor { 0%,100% { transform: translate(0,0); } 20% { transform: translate(-10px,0); } 40% { transform: translate(10px,0); } 60% { transform: translate(-10px,0); } 80% { transform: translate(10px,0); } }

/* --- Marca de agua animada para tareas completadas --- */
.watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-25deg) scale(0.8);
    font-size: 48px;
    color: rgba(0, 0, 0, 0.1);
    pointer-events: none;
    user-select: none;
    font-weight: bold;
    z-index: 1;
    opacity: 0;
    animation: watermarkAppear 1s forwards;
}

@keyframes watermarkAppear {
    0% { opacity: 0; transform: translate(-50%, -50%) rotate(-25deg) scale(0.5); }
    50% { opacity: 0.3; transform: translate(-50%, -50%) rotate(-25deg) scale(1.1); }
    100% { opacity: 0.1; transform: translate(-50%, -50%) rotate(-25deg) scale(1); }
}

.task.done { position: relative; }

</style>
</head>
<body>

<header>
  <h1>📋 Planeador Scrumban Profesional</h1>
  <div style="display:flex; align-items:center; gap:10px; position:relative;">
    <button class="btn-crear" onclick="window.location='crear_tarea.php'">➕ Crear tarea</button>
    <button class="btn-logout" onclick="window.location='logout.php'">🚪 Cerrar sesión</button>
    <button class="btn-chatss" onclick="window.location='chatss.php'">💬 Chats</button>

    <div id="notiBell">🔔<span id="notiCount"></span></div>
    <div id="notiBox"></div>

    <span class="userbar">
      👤 <?= escape($_SESSION['name'] ?? $_SESSION['username']); ?> — <?= escape($_SESSION['cargo'] ?? 'Sin cargo'); ?> — <?= ucfirst(escape($_SESSION['role'] ?? 'Sin rol')); ?>
    </span>
    <span class="userbar" id="fechaHora" style="margin-left:10px;"></span>
  </div>
</header>

<main>
<?php foreach($estados as $estado => $tareas_estado): ?>
<div class="column" ondrop="drop(event,'<?= $estado; ?>')" ondragover="allowDrop(event)">
  <h2>
    <?= match($estado) {
        'todo' => '🕓 Por hacer',
        'doing' => '⚙️ En progreso',
        'review' => '🔍 En revisión',
        'done' => '✅ Hecho'
    }; ?>
  </h2>
  <?php foreach($tareas_estado as $t): ?>
  <?php 
    $colorPrioridad = colorPorPrioridad($t['priority'] ?? 'media');
    $progreso = match($t['status'] ?? 'todo') {
        'todo' => 0,
        'doing' => 50,
        'review' => 75,
        'done' => 100,
        default => 0
    };
    $colorProgreso = $progreso <= 33 ? '#e53935' : ($progreso <= 66 ? '#ffb300' : '#43a047');

    $subtareas = obtenerSubtareas($pdo, $t['id']);
    $comentarios = obtenerComentarios($pdo, $t['id']);
  ?>
  <div class="task" draggable="true" ondragstart="drag(event)" data-id="<?= $t['id']; ?>" data-status="<?= $t['status'] ?? 'todo'; ?>" style="background:<?= $colorPrioridad; ?>;" onclick="toggleAcciones(this)">
    <strong><?= escape($t['title']); ?></strong>
    <p><?= escape($t['description']); ?></p>

    <div class="progreso">
        <div style="width:<?= $progreso; ?>%; background:<?= $colorProgreso; ?>;"></div>
    </div>

    <div class="subtareas">
        <strong>Subtareas:</strong>
        <div id="subtareas-<?= $t['id']; ?>">
            <?php foreach($subtareas as $s): ?>
                <div>➤ <b><?= escape($s['title']); ?></b> <?= $s['description'] ? '- '.escape($s['description']) : '' ?></div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="comentarios" id="comentarios-<?= $t['id']; ?>">
        <?php foreach($comentarios as $c): ?>
            <div>💬 <?= escape($c['comment']); ?></div>
        <?php endforeach; ?>
    </div>

    <div class="info">
        <small><b>Progreso:</b> <?= $progreso; ?>%</small>
       <small>📅 Fecha límite: <?= fechaEnEspanol($t['due_date'] ?? null); ?></small>
        <small>⚡ Prioridad: <?= ucfirst(escape($t['priority'] ?? 'Media')); ?></small>
        <small>👥 Asignado a: <?= escape($t['name']); ?> — <?= escape($t['cargo']); ?></small>
        <small>📝 Creada el: <?= fechaEnEspanol($t['created_at'] ?? 'now'); ?>
        — por <?= escape($t['creator_name'] ?? $_SESSION['name'] ?? $_SESSION['username']); ?>
        — <?= escape($_SESSION['cargo'] ?? 'Sin cargo'); ?>
       </small>
    </div>

    <span class="acciones-tarea" style="display:none;">
        <button onclick="crearSubtarea(<?= $t['id']; ?>)">➕ Subtarea</button>
        <button onclick="comentarTarea(<?= $t['id']; ?>)">💬 Comentar</button>
    </span>

    <button class="delete-btn" onclick="deleteTask(<?= $t['id']; ?>)">🗑</button>
  </div>
  <?php endforeach; ?>
</div>
<?php endforeach; ?>
</main>

<footer>Planeador 2025 — Derechos Reservados</footer>

<script>
// DRAG & DROP
let isDragging=false;
function allowDrop(ev){ ev.preventDefault(); }
function drag(ev){ isDragging=true; ev.dataTransfer.setData("text", ev.target.dataset.id); }
function drop(ev,status){
    ev.preventDefault();
    const id=ev.dataTransfer.getData("text");
    const task=document.querySelector(`[data-id='${id}']`);
    const column=ev.target.closest('.column');
    if(task && column){ column.appendChild(task); moveTask(id,status); }
    isDragging=false;
}

function moveTask(id,status){
    fetch('move_task.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({id,status})
    }).then(res=>res.json()).then(data=>{
        if(!data.success){ alert('No se pudo actualizar la tarea.'); } 
        else {
            const task=document.querySelector(`[data-id='${id}']`);
            if(task){
                let progreso=0,colorProgreso='#e53935';
                switch(status){
                    case 'todo': progreso=0;colorProgreso='#e53935';break;
                    case 'doing': progreso=50;colorProgreso='#ffb300';break;
                    case 'review': progreso=75;colorProgreso='#ffb300';break;
                    case 'done': progreso=100;colorProgreso='#43a047';break;
                }
                const barra=task.querySelector('.progreso > div');
                if(barra){ barra.style.width=progreso+'%'; barra.style.background=colorProgreso; }
                const progText=task.querySelector('.info small:first-child');
                if(progText){ progText.innerHTML=`<b>Progreso:</b> ${progreso}%`; }

                // --- Marca de agua ---
                if(status === 'done'){
                    if(!task.querySelector('.watermark')){
                        const wm = document.createElement('div');
                        wm.className = 'watermark';
                        wm.textContent = 'TERMINADA';
                        task.appendChild(wm);
                    }
                    task.classList.add('done');
                } else {
                    const wm = task.querySelector('.watermark');
                    if(wm) wm.remove();
                    task.classList.remove('done');
                }
            }
        }
    });
}

// FECHA/HORA
function actualizarFechaHora(){
    const opciones={weekday:'long',year:'numeric',month:'long',day:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit'};
    document.getElementById('fechaHora').textContent=new Date().toLocaleString('es-ES',opciones);
}
setInterval(actualizarFechaHora,1000); actualizarFechaHora();

// ELIMINAR TAREA
function deleteTask(id){
    if(!confirm('¿Deseas eliminar esta tarea?')) return;
    fetch('delete_task.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({id})
    }).then(res=>res.json()).then(data=>{
        if(data.success) document.querySelector(`[data-id='${id}']`)?.remove();
        else alert('Error al eliminar.');
    });
}

// SUBTAREAS Y COMENTARIOS
function crearSubtarea(id){
    const title=prompt("Título de la subtarea:");
    if(!title) return;
    const description=prompt("Descripción (opcional)","");
    fetch('guardar_subtarea.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({task_id:id,title,description})
    }).then(res=>res.json()).then(data=>{
        if(data.success){
            const cont=document.getElementById('subtareas-'+id);
            const div=document.createElement('div');
            div.innerHTML=`➤ <b>${title}</b> ${description?'- '+description:''}`;
            cont.appendChild(div);
        } else alert('Error: '+data.msg);
    });
}

function comentarTarea(id){
    const comment=prompt("Escribe tu comentario:");
    if(!comment) return;
    fetch('guardar_comentario.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({task_id:id,comment})
    }).then(res=>res.json()).then(data=>{
        if(data.success){
            const cont=document.getElementById('comentarios-'+id);
            const div=document.createElement('div');
            div.innerHTML=`💬 ${comment}`;
            cont.appendChild(div);
        } else alert('Error: '+data.msg);
    });
}

// TOGGLE BOTONES
function toggleAcciones(ficha){
    if(isDragging) return;
    const acciones=ficha.querySelector('.acciones-tarea');
    if(acciones){ acciones.style.display=acciones.style.display==='none'?'inline-flex':'none'; }
}

// NOTIFICACIONES
let lastCount=0;
const notiCount=document.getElementById('notiCount');
const notiBell=document.getElementById('notiBell');
const notiBox=document.getElementById('notiBox');

const sonidos={
    mensaje:new Audio('msg.mp3'),
    alerta:new Audio('alerta.mp3'),
    zumbido:new Audio('zumbido.mp3')
};

function crearPopup(msg){
    const popup=document.createElement('div');
    popup.className='alertaPopup';
    let icono=msg.type==='alerta'?'🚨':msg.type==='zumbido'?'📳':'💬';
    popup.innerHTML=`${icono} <b>${msg.sender_name}</b><br>${msg.message}`;
    document.body.appendChild(popup);
    setTimeout(()=>popup.remove(),5000);
}
function vibrarPantalla(){ document.body.style.animation="temblor 0.4s ease-in-out 6"; setTimeout(()=>document.body.style.animation="",2000); }

async function checkNotifications(){
    try{
        const res=await fetch('notificaciones.php');
        const data=await res.json();
        notiCount.textContent=data.length||'';
        notiCount.style.display=data.length?'block':'none';
        if(data.length>lastCount){
            data.slice(lastCount).forEach(msg=>{
                if(msg.type==='zumbido'){ sonidos.zumbido.play(); vibrarPantalla(); crearPopup(msg); }
                else if(msg.type==='alerta'){ sonidos.alerta.play(); crearPopup(msg); }
                else{ sonidos.mensaje.play(); crearPopup(msg); }
            });
        }
        lastCount=data.length;
    }catch(err){ console.error(err); }
}

notiBell.addEventListener('click',async ()=>{
    if(notiBox.style.display==='block'){ notiBox.style.display='none'; return; }
    try{
        const res=await fetch('notificaciones.php');
        const data=await res.json();
        if(data.length){
            notiBox.innerHTML=data.map(n=>`<div style='border-bottom:1px solid #ccc;padding:5px 0;'>${n.sender_name}: ${n.message}</div>`).join('');
        }else{
            notiBox.innerHTML='<em>Sin notificaciones nuevas</em>';
        }
        notiBox.style.display='block';
        notiCount.style.display='none';
        lastCount=0;
    }catch(err){ console.error(err); }
});

setInterval(checkNotifications,4000);
</script>
</body>
</html>











