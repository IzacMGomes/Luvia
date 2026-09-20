/* ============================================
   Trilha — animações da landing page
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {

    /* ---------- 1. Navbar muda de estilo ao rolar a página ---------- */
    const navbar = document.getElementById('navbar');
    const aoRolar = () => {
        if (window.scrollY > 12) {
            navbar.classList.add('rolou');
        } else {
            navbar.classList.remove('rolou');
        }
    };
    window.addEventListener('scroll', aoRolar);
    aoRolar();

    /* ---------- 2. Menu mobile (hambúrguer) ---------- */
    const botaoMenu = document.getElementById('botaoMenuMobile');
    const menuMobile = document.getElementById('menuMobile');
    if (botaoMenu && menuMobile) {
        botaoMenu.addEventListener('click', () => {
            menuMobile.classList.toggle('aberto');
        });
        // Fecha o menu ao clicar em qualquer link dele
        menuMobile.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => menuMobile.classList.remove('aberto'));
        });
    }

    /* ---------- 3. Revelar elementos ao rolar a página (fade + slide up) ---------- */
    const elementosRevelar = document.querySelectorAll('.revelar');
    const observerRevelar = new IntersectionObserver((entradas) => {
        entradas.forEach((entrada, indice) => {
            if (entrada.isIntersecting) {
                // Pequeno atraso em cascata para elementos que aparecem juntos (ex.: cards de curso)
                const atraso = Array.from(entrada.target.parentElement.children).indexOf(entrada.target) * 90;
                setTimeout(() => entrada.target.classList.add('visivel'), atraso);
                observerRevelar.unobserve(entrada.target);
            }
        });
    }, { threshold: 0.15 });
    elementosRevelar.forEach((el) => observerRevelar.observe(el));

    /* ---------- 4. Sequência animada de entrada do hero (cards flutuantes + selo) ---------- */
    const cardConcluida = document.getElementById('cardConcluida');
    const cardProgresso = document.getElementById('cardProgresso');
    const seloGratis = document.getElementById('seloGratis');
    const barraProgresso = document.getElementById('barraProgresso');
    const textoProgresso = document.getElementById('textoProgresso');

    function animarContadorProgresso(alvo, duracaoMs) {
        const inicio = performance.now();
        function passo(agora) {
            const decorrido = Math.min((agora - inicio) / duracaoMs, 1);
            const valorAtual = Math.round(alvo * decorrido);
            textoProgresso.textContent = valorAtual + '% concluído';
            if (decorrido < 1) requestAnimationFrame(passo);
        }
        requestAnimationFrame(passo);
    }

    function iniciarSequenciaHero() {
        // 1) selo "Grátis" aparece com leve zoom
        seloGratis.style.transition = 'opacity .5s ease, transform .5s cubic-bezier(.34,1.56,.64,1)';
        seloGratis.style.transform = 'scale(0.6)';
        requestAnimationFrame(() => {
            seloGratis.style.opacity = '1';
            seloGratis.style.transform = 'scale(1)';
        });

        // 2) card "aula concluída" desliza e aparece
        setTimeout(() => {
            cardConcluida.style.transition = 'opacity .5s ease, transform .5s ease';
            cardConcluida.style.transform = 'translateY(0)';
            cardConcluida.style.opacity = '1';
        }, 350);

        // 3) card de progresso aparece e a barra + porcentagem animam até 75%
        setTimeout(() => {
            cardProgresso.style.transition = 'opacity .5s ease, transform .5s ease';
            cardProgresso.style.transform = 'translateY(0)';
            cardProgresso.style.opacity = '1';
            barraProgresso.style.width = '75%';
            animarContadorProgresso(75, 1200);
        }, 700);

        // 4) depois da entrada, os cards ficam com um flutuar suave e contínuo
        setTimeout(() => {
            cardConcluida.style.transition = 'transform 3.2s ease-in-out';
            cardProgresso.style.transition = 'transform 3.6s ease-in-out';
            let sobe = true;
            setInterval(() => {
                cardConcluida.style.transform = sobe ? 'translateY(-8px)' : 'translateY(0)';
                cardProgresso.style.transform = sobe ? 'translateY(6px)' : 'translateY(0)';
                sobe = !sobe;
            }, 3200);
        }, 1400);
    }
    // Posições iniciais (fora de vista) antes da animação de entrada
    cardConcluida.style.transform = 'translateY(14px)';
    cardProgresso.style.transform = 'translateY(14px)';
    window.addEventListener('load', () => setTimeout(iniciarSequenciaHero, 250));

    /* ---------- 5. Contadores animados na seção "Comunidade" ---------- */
    const contadores = document.querySelectorAll('.contador');
    const observerContadores = new IntersectionObserver((entradas) => {
        entradas.forEach((entrada) => {
            if (!entrada.isIntersecting) return;
            const elemento = entrada.target;
            const alvo = parseInt(elemento.dataset.alvo, 10);
            const duracao = 1400;
            const inicio = performance.now();
            function passo(agora) {
                const decorrido = Math.min((agora - inicio) / duracao, 1);
                // easing suave de desaceleração
                const progresso = 1 - Math.pow(1 - decorrido, 3);
                elemento.textContent = Math.round(alvo * progresso).toLocaleString('pt-BR');
                if (decorrido < 1) requestAnimationFrame(passo);
            }
            requestAnimationFrame(passo);
            observerContadores.unobserve(elemento);
        });
    }, { threshold: 0.5 });
    contadores.forEach((c) => observerContadores.observe(c));

    /* ---------- 6. Rolagem suave para os links do menu (com offset da navbar fixa) ---------- */
    document.querySelectorAll('a[href^="#"]').forEach((link) => {
        link.addEventListener('click', (evento) => {
            const destino = document.querySelector(link.getAttribute('href'));
            if (!destino) return;
            evento.preventDefault();
            const offset = 76; // altura da navbar fixa
            const posicao = destino.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top: posicao, behavior: 'smooth' });
        });
    });
});
