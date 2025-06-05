--
-- PostgreSQL database dump
--

-- Dumped from database version 16.4
-- Dumped by pg_dump version 16.2

-- Started on 2025-06-05 09:12:17

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 5994 (class 1262 OID 32768)
-- Name: sig; Type: DATABASE; Schema: -; Owner: postgres
--

CREATE DATABASE sig WITH TEMPLATE = template0 ENCODING = 'UTF8' LOCALE_PROVIDER = libc LOCALE = 'Spanish_Spain.1252';


ALTER DATABASE sig OWNER TO postgres;

\connect sig

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 5995 (class 0 OID 0)
-- Dependencies: 5994
-- Name: DATABASE sig; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON DATABASE sig IS 'Actiualizacion de CUIS
';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 242 (class 1259 OID 53336)
-- Name: coordenadas; Type: TABLE; Schema: cuis; Owner: postgres
--

CREATE TABLE cuis.coordenadas (
    id integer NOT NULL,
    x_gkba numeric NOT NULL,
    y_gkba numeric NOT NULL,
    x_wgs84 numeric NOT NULL,
    y_wgs84 numeric NOT NULL,
    geom_gkba public.geometry,
    geom_wgs84 public.geometry
);


ALTER TABLE cuis.coordenadas OWNER TO postgres;

--
-- TOC entry 241 (class 1259 OID 53335)
-- Name: coordenadas_id_seq; Type: SEQUENCE; Schema: cuis; Owner: postgres
--

CREATE SEQUENCE cuis.coordenadas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cuis.coordenadas_id_seq OWNER TO postgres;

--
-- TOC entry 5996 (class 0 OID 0)
-- Dependencies: 241
-- Name: coordenadas_id_seq; Type: SEQUENCE OWNED BY; Schema: cuis; Owner: postgres
--

ALTER SEQUENCE cuis.coordenadas_id_seq OWNED BY cuis.coordenadas.id;


--
-- TOC entry 234 (class 1259 OID 53287)
-- Name: direcciones; Type: TABLE; Schema: cuis; Owner: postgres
--

CREATE TABLE cuis.direcciones (
    id integer NOT NULL,
    codigo_calle text NOT NULL,
    calle text NOT NULL,
    altura text NOT NULL,
    ubicacion_administrativa_id integer,
    parcela_id integer,
    coordenadas_id integer
);


ALTER TABLE cuis.direcciones OWNER TO postgres;

--
-- TOC entry 233 (class 1259 OID 53286)
-- Name: direcciones_id_seq; Type: SEQUENCE; Schema: cuis; Owner: postgres
--

CREATE SEQUENCE cuis.direcciones_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cuis.direcciones_id_seq OWNER TO postgres;

--
-- TOC entry 5997 (class 0 OID 0)
-- Dependencies: 233
-- Name: direcciones_id_seq; Type: SEQUENCE OWNED BY; Schema: cuis; Owner: postgres
--

ALTER SEQUENCE cuis.direcciones_id_seq OWNED BY cuis.direcciones.id;


--
-- TOC entry 248 (class 1259 OID 53384)
-- Name: edificios; Type: TABLE; Schema: cuis; Owner: postgres
--

CREATE TABLE cuis.edificios (
    id integer NOT NULL,
    cui text NOT NULL,
    estado text NOT NULL,
    sector text NOT NULL,
    predio_id integer,
    x_gkba numeric,
    y_gkba numeric,
    gestionado boolean NOT NULL,
    institucion text,
    fecha_creacion timestamp without time zone DEFAULT now(),
    x_wgs84 numeric(10,6),
    y_wgs84 numeric(10,6),
    geom public.geometry,
    CONSTRAINT edificios_estado_check CHECK ((estado = ANY (ARRAY['activo'::text, 'inactivo'::text, 'baja'::text, 'proyecto'::text, 'otro'::text, 'sin datos'::text]))),
    CONSTRAINT edificios_sector_check CHECK ((sector = ANY (ARRAY['publico'::text, 'privado'::text, 'otro'::text, 'sin datos'::text])))
);


ALTER TABLE cuis.edificios OWNER TO postgres;

--
-- TOC entry 250 (class 1259 OID 53403)
-- Name: edificios_direcciones; Type: TABLE; Schema: cuis; Owner: postgres
--

CREATE TABLE cuis.edificios_direcciones (
    id integer NOT NULL,
    edificio_id integer,
    direccion_id integer,
    es_principal boolean DEFAULT false
);


ALTER TABLE cuis.edificios_direcciones OWNER TO postgres;

--
-- TOC entry 249 (class 1259 OID 53402)
-- Name: edificios_direcciones_id_seq; Type: SEQUENCE; Schema: cuis; Owner: postgres
--

CREATE SEQUENCE cuis.edificios_direcciones_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cuis.edificios_direcciones_id_seq OWNER TO postgres;

--
-- TOC entry 5998 (class 0 OID 0)
-- Dependencies: 249
-- Name: edificios_direcciones_id_seq; Type: SEQUENCE OWNED BY; Schema: cuis; Owner: postgres
--

ALTER SEQUENCE cuis.edificios_direcciones_id_seq OWNED BY cuis.edificios_direcciones.id;


--
-- TOC entry 247 (class 1259 OID 53383)
-- Name: edificios_id_seq; Type: SEQUENCE; Schema: cuis; Owner: postgres
--

CREATE SEQUENCE cuis.edificios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cuis.edificios_id_seq OWNER TO postgres;

--
-- TOC entry 5999 (class 0 OID 0)
-- Dependencies: 247
-- Name: edificios_id_seq; Type: SEQUENCE OWNED BY; Schema: cuis; Owner: postgres
--

ALTER SEQUENCE cuis.edificios_id_seq OWNED BY cuis.edificios.id;


--
-- TOC entry 258 (class 1259 OID 53469)
-- Name: observaciones; Type: TABLE; Schema: cuis; Owner: postgres
--

CREATE TABLE cuis.observaciones (
    id integer NOT NULL,
    edificio_id integer,
    usuario_id integer,
    observacion text NOT NULL,
    fecha_observacion timestamp without time zone DEFAULT now()
);


ALTER TABLE cuis.observaciones OWNER TO postgres;

--
-- TOC entry 257 (class 1259 OID 53468)
-- Name: observaciones_id_seq; Type: SEQUENCE; Schema: cuis; Owner: postgres
--

CREATE SEQUENCE cuis.observaciones_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cuis.observaciones_id_seq OWNER TO postgres;

--
-- TOC entry 6000 (class 0 OID 0)
-- Dependencies: 257
-- Name: observaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: cuis; Owner: postgres
--

ALTER SEQUENCE cuis.observaciones_id_seq OWNED BY cuis.observaciones.id;


--
-- TOC entry 252 (class 1259 OID 53423)
-- Name: operativos; Type: TABLE; Schema: cuis; Owner: postgres
--

CREATE TABLE cuis.operativos (
    id integer NOT NULL,
    nombre text NOT NULL
);


ALTER TABLE cuis.operativos OWNER TO postgres;

--
-- TOC entry 251 (class 1259 OID 53422)
-- Name: operativos_id_seq; Type: SEQUENCE; Schema: cuis; Owner: postgres
--

CREATE SEQUENCE cuis.operativos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cuis.operativos_id_seq OWNER TO postgres;

--
-- TOC entry 6001 (class 0 OID 0)
-- Dependencies: 251
-- Name: operativos_id_seq; Type: SEQUENCE OWNED BY; Schema: cuis; Owner: postgres
--

ALTER SEQUENCE cuis.operativos_id_seq OWNED BY cuis.operativos.id;


--
-- TOC entry 238 (class 1259 OID 53311)
-- Name: parcelas; Type: TABLE; Schema: cuis; Owner: postgres
--

CREATE TABLE cuis.parcelas (
    id integer NOT NULL,
    smp text NOT NULL,
    seccion text,
    manzana text,
    parcela text,
    superficie_total numeric,
    superficie_cubierta numeric,
    frente numeric,
    fondo numeric,
    propiedad_horizontal boolean,
    pisos_bajo_rasante integer,
    pisos_sobre_rasante integer,
    unidades_funcionales integer,
    locales integer
);


ALTER TABLE cuis.parcelas OWNER TO postgres;

--
-- TOC entry 237 (class 1259 OID 53310)
-- Name: parcelas_id_seq; Type: SEQUENCE; Schema: cuis; Owner: postgres
--

CREATE SEQUENCE cuis.parcelas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cuis.parcelas_id_seq OWNER TO postgres;

--
-- TOC entry 6002 (class 0 OID 0)
-- Dependencies: 237
-- Name: parcelas_id_seq; Type: SEQUENCE OWNED BY; Schema: cuis; Owner: postgres
--

ALTER SEQUENCE cuis.parcelas_id_seq OWNED BY cuis.parcelas.id;


--
-- TOC entry 246 (class 1259 OID 53373)
-- Name: predios; Type: TABLE; Schema: cuis; Owner: postgres
--

CREATE TABLE cuis.predios (
    id integer NOT NULL,
    cup text NOT NULL,
    nombre text
);


ALTER TABLE cuis.predios OWNER TO postgres;

--
-- TOC entry 245 (class 1259 OID 53372)
-- Name: predios_id_seq; Type: SEQUENCE; Schema: cuis; Owner: postgres
--

CREATE SEQUENCE cuis.predios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cuis.predios_id_seq OWNER TO postgres;

--
-- TOC entry 6003 (class 0 OID 0)
-- Dependencies: 245
-- Name: predios_id_seq; Type: SEQUENCE OWNED BY; Schema: cuis; Owner: postgres
--

ALTER SEQUENCE cuis.predios_id_seq OWNED BY cuis.predios.id;


--
-- TOC entry 240 (class 1259 OID 53322)
-- Name: puertas; Type: TABLE; Schema: cuis; Owner: postgres
--

CREATE TABLE cuis.puertas (
    id integer NOT NULL,
    parcela_id integer NOT NULL,
    codigo_calle text NOT NULL,
    calle text NOT NULL,
    altura text NOT NULL,
    puerta_principal boolean,
    puerta_oficial boolean,
    fuente text,
    fecha_actualizacion date
);


ALTER TABLE cuis.puertas OWNER TO postgres;

--
-- TOC entry 239 (class 1259 OID 53321)
-- Name: puertas_id_seq; Type: SEQUENCE; Schema: cuis; Owner: postgres
--

CREATE SEQUENCE cuis.puertas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cuis.puertas_id_seq OWNER TO postgres;

--
-- TOC entry 6004 (class 0 OID 0)
-- Dependencies: 239
-- Name: puertas_id_seq; Type: SEQUENCE OWNED BY; Schema: cuis; Owner: postgres
--

ALTER SEQUENCE cuis.puertas_id_seq OWNED BY cuis.puertas.id;


--
-- TOC entry 256 (class 1259 OID 53451)
-- Name: registro_cambios; Type: TABLE; Schema: cuis; Owner: postgres
--

CREATE TABLE cuis.registro_cambios (
    id integer NOT NULL,
    edificio_id integer,
    usuario_id integer,
    fecha_cambio timestamp without time zone DEFAULT now()
);


ALTER TABLE cuis.registro_cambios OWNER TO postgres;

--
-- TOC entry 255 (class 1259 OID 53450)
-- Name: registro_cambios_id_seq; Type: SEQUENCE; Schema: cuis; Owner: postgres
--

CREATE SEQUENCE cuis.registro_cambios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cuis.registro_cambios_id_seq OWNER TO postgres;

--
-- TOC entry 6005 (class 0 OID 0)
-- Dependencies: 255
-- Name: registro_cambios_id_seq; Type: SEQUENCE OWNED BY; Schema: cuis; Owner: postgres
--

ALTER SEQUENCE cuis.registro_cambios_id_seq OWNED BY cuis.registro_cambios.id;


--
-- TOC entry 254 (class 1259 OID 53434)
-- Name: relevamientos; Type: TABLE; Schema: cuis; Owner: postgres
--

CREATE TABLE cuis.relevamientos (
    id integer NOT NULL,
    edificio_id integer,
    operativo_id integer,
    relevado boolean NOT NULL
);


ALTER TABLE cuis.relevamientos OWNER TO postgres;

--
-- TOC entry 253 (class 1259 OID 53433)
-- Name: relevamientos_id_seq; Type: SEQUENCE; Schema: cuis; Owner: postgres
--

CREATE SEQUENCE cuis.relevamientos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cuis.relevamientos_id_seq OWNER TO postgres;

--
-- TOC entry 6006 (class 0 OID 0)
-- Dependencies: 253
-- Name: relevamientos_id_seq; Type: SEQUENCE OWNED BY; Schema: cuis; Owner: postgres
--

ALTER SEQUENCE cuis.relevamientos_id_seq OWNED BY cuis.relevamientos.id;


--
-- TOC entry 236 (class 1259 OID 53302)
-- Name: ubicacion_administrativa; Type: TABLE; Schema: cuis; Owner: postgres
--

CREATE TABLE cuis.ubicacion_administrativa (
    id integer NOT NULL,
    comuna text,
    barrio text,
    comisaria text,
    area_hospitalaria text,
    region_sanitaria text,
    distrito_escolar text,
    comisaria_vecinal text,
    seccion_catastral text,
    codigo_postal text,
    codigo_postal_argentino text
);


ALTER TABLE cuis.ubicacion_administrativa OWNER TO postgres;

--
-- TOC entry 235 (class 1259 OID 53301)
-- Name: ubicacion_administrativa_id_seq; Type: SEQUENCE; Schema: cuis; Owner: postgres
--

CREATE SEQUENCE cuis.ubicacion_administrativa_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cuis.ubicacion_administrativa_id_seq OWNER TO postgres;

--
-- TOC entry 6007 (class 0 OID 0)
-- Dependencies: 235
-- Name: ubicacion_administrativa_id_seq; Type: SEQUENCE OWNED BY; Schema: cuis; Owner: postgres
--

ALTER SEQUENCE cuis.ubicacion_administrativa_id_seq OWNED BY cuis.ubicacion_administrativa.id;


--
-- TOC entry 244 (class 1259 OID 53360)
-- Name: usuarios; Type: TABLE; Schema: cuis; Owner: postgres
--

CREATE TABLE cuis.usuarios (
    id integer NOT NULL,
    nombre text NOT NULL,
    email text NOT NULL,
    password_hash text NOT NULL,
    rol text NOT NULL,
    fecha_creacion timestamp without time zone DEFAULT now(),
    estado boolean DEFAULT true,
    CONSTRAINT usuarios_rol_check CHECK ((rol = ANY (ARRAY['admin'::text, 'editor'::text, 'visualizador'::text])))
);


ALTER TABLE cuis.usuarios OWNER TO postgres;

--
-- TOC entry 243 (class 1259 OID 53359)
-- Name: usuarios_id_seq; Type: SEQUENCE; Schema: cuis; Owner: postgres
--

CREATE SEQUENCE cuis.usuarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cuis.usuarios_id_seq OWNER TO postgres;

--
-- TOC entry 6008 (class 0 OID 0)
-- Dependencies: 243
-- Name: usuarios_id_seq; Type: SEQUENCE OWNED BY; Schema: cuis; Owner: postgres
--

ALTER SEQUENCE cuis.usuarios_id_seq OWNED BY cuis.usuarios.id;


--
-- TOC entry 265 (class 1259 OID 53974)
-- Name: v_edificios_relevamientos; Type: VIEW; Schema: cuis; Owner: postgres
--

CREATE VIEW cuis.v_edificios_relevamientos AS
 SELECT e.id AS id_edificio,
    e.cui,
        CASE
            WHEN (r1.id IS NOT NULL) THEN 'relevado'::text
            ELSE 'no relevado'::text
        END AS operativo_1,
        CASE
            WHEN (r2.id IS NOT NULL) THEN 'relevado'::text
            ELSE 'no relevado'::text
        END AS operativo_2
   FROM ((cuis.edificios e
     LEFT JOIN cuis.relevamientos r1 ON (((r1.edificio_id = e.id) AND (r1.operativo_id = 1))))
     LEFT JOIN cuis.relevamientos r2 ON (((r2.edificio_id = e.id) AND (r2.operativo_id = 2))))
  ORDER BY e.id;


ALTER VIEW cuis.v_edificios_relevamientos OWNER TO postgres;

--
-- TOC entry 266 (class 1259 OID 53989)
-- Name: v_padron_cui; Type: VIEW; Schema: cuis; Owner: postgres
--

CREATE VIEW cuis.v_padron_cui AS
 SELECT cui,
    estado,
    sector,
    x_gkba,
    y_gkba
   FROM cuis.edificios
  ORDER BY cui;


ALTER VIEW cuis.v_padron_cui OWNER TO postgres;

--
-- TOC entry 5770 (class 2604 OID 53339)
-- Name: coordenadas id; Type: DEFAULT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.coordenadas ALTER COLUMN id SET DEFAULT nextval('cuis.coordenadas_id_seq'::regclass);


--
-- TOC entry 5766 (class 2604 OID 53290)
-- Name: direcciones id; Type: DEFAULT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.direcciones ALTER COLUMN id SET DEFAULT nextval('cuis.direcciones_id_seq'::regclass);


--
-- TOC entry 5775 (class 2604 OID 53387)
-- Name: edificios id; Type: DEFAULT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.edificios ALTER COLUMN id SET DEFAULT nextval('cuis.edificios_id_seq'::regclass);


--
-- TOC entry 5777 (class 2604 OID 53406)
-- Name: edificios_direcciones id; Type: DEFAULT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.edificios_direcciones ALTER COLUMN id SET DEFAULT nextval('cuis.edificios_direcciones_id_seq'::regclass);


--
-- TOC entry 5783 (class 2604 OID 53472)
-- Name: observaciones id; Type: DEFAULT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.observaciones ALTER COLUMN id SET DEFAULT nextval('cuis.observaciones_id_seq'::regclass);


--
-- TOC entry 5779 (class 2604 OID 53426)
-- Name: operativos id; Type: DEFAULT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.operativos ALTER COLUMN id SET DEFAULT nextval('cuis.operativos_id_seq'::regclass);


--
-- TOC entry 5768 (class 2604 OID 53314)
-- Name: parcelas id; Type: DEFAULT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.parcelas ALTER COLUMN id SET DEFAULT nextval('cuis.parcelas_id_seq'::regclass);


--
-- TOC entry 5774 (class 2604 OID 53376)
-- Name: predios id; Type: DEFAULT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.predios ALTER COLUMN id SET DEFAULT nextval('cuis.predios_id_seq'::regclass);


--
-- TOC entry 5769 (class 2604 OID 53325)
-- Name: puertas id; Type: DEFAULT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.puertas ALTER COLUMN id SET DEFAULT nextval('cuis.puertas_id_seq'::regclass);


--
-- TOC entry 5781 (class 2604 OID 53454)
-- Name: registro_cambios id; Type: DEFAULT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.registro_cambios ALTER COLUMN id SET DEFAULT nextval('cuis.registro_cambios_id_seq'::regclass);


--
-- TOC entry 5780 (class 2604 OID 53437)
-- Name: relevamientos id; Type: DEFAULT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.relevamientos ALTER COLUMN id SET DEFAULT nextval('cuis.relevamientos_id_seq'::regclass);


--
-- TOC entry 5767 (class 2604 OID 53305)
-- Name: ubicacion_administrativa id; Type: DEFAULT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.ubicacion_administrativa ALTER COLUMN id SET DEFAULT nextval('cuis.ubicacion_administrativa_id_seq'::regclass);


--
-- TOC entry 5771 (class 2604 OID 53363)
-- Name: usuarios id; Type: DEFAULT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.usuarios ALTER COLUMN id SET DEFAULT nextval('cuis.usuarios_id_seq'::regclass);


--
-- TOC entry 5799 (class 2606 OID 53343)
-- Name: coordenadas coordenadas_pkey; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.coordenadas
    ADD CONSTRAINT coordenadas_pkey PRIMARY KEY (id);


--
-- TOC entry 5789 (class 2606 OID 53294)
-- Name: direcciones direcciones_pkey; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.direcciones
    ADD CONSTRAINT direcciones_pkey PRIMARY KEY (id);


--
-- TOC entry 5809 (class 2606 OID 53396)
-- Name: edificios edificios_cui_key; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.edificios
    ADD CONSTRAINT edificios_cui_key UNIQUE (cui);


--
-- TOC entry 5813 (class 2606 OID 53409)
-- Name: edificios_direcciones edificios_direcciones_pkey; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.edificios_direcciones
    ADD CONSTRAINT edificios_direcciones_pkey PRIMARY KEY (id);


--
-- TOC entry 5811 (class 2606 OID 53394)
-- Name: edificios edificios_pkey; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.edificios
    ADD CONSTRAINT edificios_pkey PRIMARY KEY (id);


--
-- TOC entry 5825 (class 2606 OID 53477)
-- Name: observaciones observaciones_pkey; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.observaciones
    ADD CONSTRAINT observaciones_pkey PRIMARY KEY (id);


--
-- TOC entry 5817 (class 2606 OID 53432)
-- Name: operativos operativos_nombre_key; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.operativos
    ADD CONSTRAINT operativos_nombre_key UNIQUE (nombre);


--
-- TOC entry 5819 (class 2606 OID 53430)
-- Name: operativos operativos_pkey; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.operativos
    ADD CONSTRAINT operativos_pkey PRIMARY KEY (id);


--
-- TOC entry 5793 (class 2606 OID 53318)
-- Name: parcelas parcelas_pkey; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.parcelas
    ADD CONSTRAINT parcelas_pkey PRIMARY KEY (id);


--
-- TOC entry 5795 (class 2606 OID 53320)
-- Name: parcelas parcelas_smp_key; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.parcelas
    ADD CONSTRAINT parcelas_smp_key UNIQUE (smp);


--
-- TOC entry 5805 (class 2606 OID 53382)
-- Name: predios predios_cup_key; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.predios
    ADD CONSTRAINT predios_cup_key UNIQUE (cup);


--
-- TOC entry 5807 (class 2606 OID 53380)
-- Name: predios predios_pkey; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.predios
    ADD CONSTRAINT predios_pkey PRIMARY KEY (id);


--
-- TOC entry 5797 (class 2606 OID 53329)
-- Name: puertas puertas_pkey; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.puertas
    ADD CONSTRAINT puertas_pkey PRIMARY KEY (id);


--
-- TOC entry 5823 (class 2606 OID 53457)
-- Name: registro_cambios registro_cambios_pkey; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.registro_cambios
    ADD CONSTRAINT registro_cambios_pkey PRIMARY KEY (id);


--
-- TOC entry 5821 (class 2606 OID 53439)
-- Name: relevamientos relevamientos_pkey; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.relevamientos
    ADD CONSTRAINT relevamientos_pkey PRIMARY KEY (id);


--
-- TOC entry 5791 (class 2606 OID 53309)
-- Name: ubicacion_administrativa ubicacion_administrativa_pkey; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.ubicacion_administrativa
    ADD CONSTRAINT ubicacion_administrativa_pkey PRIMARY KEY (id);


--
-- TOC entry 5815 (class 2606 OID 53411)
-- Name: edificios_direcciones unica_principal; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.edificios_direcciones
    ADD CONSTRAINT unica_principal UNIQUE (edificio_id, es_principal);


--
-- TOC entry 5801 (class 2606 OID 53371)
-- Name: usuarios usuarios_email_key; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.usuarios
    ADD CONSTRAINT usuarios_email_key UNIQUE (email);


--
-- TOC entry 5803 (class 2606 OID 53369)
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);


--
-- TOC entry 5831 (class 2606 OID 53417)
-- Name: edificios_direcciones edificios_direcciones_direccion_id_fkey; Type: FK CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.edificios_direcciones
    ADD CONSTRAINT edificios_direcciones_direccion_id_fkey FOREIGN KEY (direccion_id) REFERENCES cuis.direcciones(id) ON DELETE CASCADE;


--
-- TOC entry 5832 (class 2606 OID 53412)
-- Name: edificios_direcciones edificios_direcciones_edificio_id_fkey; Type: FK CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.edificios_direcciones
    ADD CONSTRAINT edificios_direcciones_edificio_id_fkey FOREIGN KEY (edificio_id) REFERENCES cuis.edificios(id) ON DELETE CASCADE;


--
-- TOC entry 5830 (class 2606 OID 53397)
-- Name: edificios edificios_predio_id_fkey; Type: FK CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.edificios
    ADD CONSTRAINT edificios_predio_id_fkey FOREIGN KEY (predio_id) REFERENCES cuis.predios(id) ON DELETE SET NULL;


--
-- TOC entry 5826 (class 2606 OID 53354)
-- Name: direcciones fk_coordenadas; Type: FK CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.direcciones
    ADD CONSTRAINT fk_coordenadas FOREIGN KEY (coordenadas_id) REFERENCES cuis.coordenadas(id) ON DELETE SET NULL;


--
-- TOC entry 5827 (class 2606 OID 53349)
-- Name: direcciones fk_parcela; Type: FK CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.direcciones
    ADD CONSTRAINT fk_parcela FOREIGN KEY (parcela_id) REFERENCES cuis.parcelas(id) ON DELETE SET NULL;


--
-- TOC entry 5828 (class 2606 OID 53344)
-- Name: direcciones fk_ubicacion; Type: FK CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.direcciones
    ADD CONSTRAINT fk_ubicacion FOREIGN KEY (ubicacion_administrativa_id) REFERENCES cuis.ubicacion_administrativa(id) ON DELETE SET NULL;


--
-- TOC entry 5837 (class 2606 OID 53478)
-- Name: observaciones observaciones_edificio_id_fkey; Type: FK CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.observaciones
    ADD CONSTRAINT observaciones_edificio_id_fkey FOREIGN KEY (edificio_id) REFERENCES cuis.edificios(id) ON DELETE CASCADE;


--
-- TOC entry 5838 (class 2606 OID 53483)
-- Name: observaciones observaciones_usuario_id_fkey; Type: FK CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.observaciones
    ADD CONSTRAINT observaciones_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES cuis.usuarios(id) ON DELETE SET NULL;


--
-- TOC entry 5829 (class 2606 OID 53330)
-- Name: puertas puertas_parcela_id_fkey; Type: FK CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.puertas
    ADD CONSTRAINT puertas_parcela_id_fkey FOREIGN KEY (parcela_id) REFERENCES cuis.parcelas(id) ON DELETE CASCADE;


--
-- TOC entry 5835 (class 2606 OID 53458)
-- Name: registro_cambios registro_cambios_edificio_id_fkey; Type: FK CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.registro_cambios
    ADD CONSTRAINT registro_cambios_edificio_id_fkey FOREIGN KEY (edificio_id) REFERENCES cuis.edificios(id) ON DELETE CASCADE;


--
-- TOC entry 5836 (class 2606 OID 53463)
-- Name: registro_cambios registro_cambios_usuario_id_fkey; Type: FK CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.registro_cambios
    ADD CONSTRAINT registro_cambios_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES cuis.usuarios(id) ON DELETE SET NULL;


--
-- TOC entry 5833 (class 2606 OID 53440)
-- Name: relevamientos relevamientos_edificio_id_fkey; Type: FK CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.relevamientos
    ADD CONSTRAINT relevamientos_edificio_id_fkey FOREIGN KEY (edificio_id) REFERENCES cuis.edificios(id) ON DELETE CASCADE;


--
-- TOC entry 5834 (class 2606 OID 53445)
-- Name: relevamientos relevamientos_operativo_id_fkey; Type: FK CONSTRAINT; Schema: cuis; Owner: postgres
--

ALTER TABLE ONLY cuis.relevamientos
    ADD CONSTRAINT relevamientos_operativo_id_fkey FOREIGN KEY (operativo_id) REFERENCES cuis.operativos(id) ON DELETE CASCADE;


-- Completed on 2025-06-05 09:12:17

--
-- PostgreSQL database dump complete
--

