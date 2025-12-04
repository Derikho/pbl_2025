--
-- PostgreSQL database dump
--

\restrict jRgTLxwDs7h4hNUtvbCUtBLZ7zBKasulLAyCDcMYzqu7ho3lwoa8xRRh9i7Idoe

-- Dumped from database version 17.6
-- Dumped by pg_dump version 17.6

-- Started on 2025-12-04 19:50:31

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 919 (class 1247 OID 17708)
-- Name: asset_category; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.asset_category AS ENUM (
    'tool',
    'room'
);


ALTER TYPE public.asset_category OWNER TO postgres;

--
-- TOC entry 880 (class 1247 OID 17502)
-- Name: content_status; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.content_status AS ENUM (
    'draft',
    'published',
    'archived'
);


ALTER TYPE public.content_status OWNER TO postgres;

--
-- TOC entry 877 (class 1247 OID 17490)
-- Name: loan_status; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.loan_status AS ENUM (
    'pending',
    'approved',
    'rejected',
    'returned',
    'overdue'
);


ALTER TYPE public.loan_status OWNER TO postgres;

--
-- TOC entry 874 (class 1247 OID 17482)
-- Name: student_type; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.student_type AS ENUM (
    'regular',
    'magang',
    'skripsi'
);


ALTER TYPE public.student_type OWNER TO postgres;

--
-- TOC entry 871 (class 1247 OID 17477)
-- Name: user_role; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.user_role AS ENUM (
    'admin',
    'member',
    'dosen'
);


ALTER TYPE public.user_role OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 240 (class 1259 OID 17690)
-- Name: activities; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.activities (
    activity_id integer NOT NULL,
    activity_type character varying(50) NOT NULL,
    title character varying(255) NOT NULL,
    description text,
    user_id integer NOT NULL,
    activity_date date NOT NULL,
    location character varying(255),
    status character varying(20) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    link character varying(255),
    CONSTRAINT activities_activity_type_check CHECK (((activity_type)::text = ANY ((ARRAY['Research'::character varying, 'Conference'::character varying, 'Workshop'::character varying, 'Seminar'::character varying, 'Other'::character varying])::text[]))),
    CONSTRAINT activities_status_check CHECK (((status)::text = ANY ((ARRAY['completed'::character varying, 'ongoing'::character varying, 'planned'::character varying, 'cancelled'::character varying])::text[])))
);


ALTER TABLE public.activities OWNER TO postgres;

--
-- TOC entry 239 (class 1259 OID 17689)
-- Name: activities_activity_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.activities_activity_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.activities_activity_id_seq OWNER TO postgres;

--
-- TOC entry 5044 (class 0 OID 0)
-- Dependencies: 239
-- Name: activities_activity_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.activities_activity_id_seq OWNED BY public.activities.activity_id;


--
-- TOC entry 242 (class 1259 OID 17714)
-- Name: assets; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.assets (
    asset_id integer NOT NULL,
    name character varying(100) NOT NULL,
    category public.asset_category NOT NULL,
    description text,
    total_quantity integer DEFAULT 1,
    available_quantity integer DEFAULT 1,
    capacity integer DEFAULT 0,
    image_url character varying(255),
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.assets OWNER TO postgres;

--
-- TOC entry 241 (class 1259 OID 17713)
-- Name: assets_asset_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.assets_asset_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.assets_asset_id_seq OWNER TO postgres;

--
-- TOC entry 5045 (class 0 OID 0)
-- Dependencies: 241
-- Name: assets_asset_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.assets_asset_id_seq OWNED BY public.assets.asset_id;


--
-- TOC entry 222 (class 1259 OID 17563)
-- Name: attendance_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.attendance_logs (
    log_id integer NOT NULL,
    user_id integer,
    date date DEFAULT CURRENT_DATE,
    check_in_time time without time zone,
    check_out_time time without time zone,
    photo_proof character varying(255),
    location_note text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.attendance_logs OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 17562)
-- Name: attendance_logs_log_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.attendance_logs_log_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.attendance_logs_log_id_seq OWNER TO postgres;

--
-- TOC entry 5046 (class 0 OID 0)
-- Dependencies: 221
-- Name: attendance_logs_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.attendance_logs_log_id_seq OWNED BY public.attendance_logs.log_id;


--
-- TOC entry 230 (class 1259 OID 17620)
-- Name: carousel_banners; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.carousel_banners (
    banner_id integer NOT NULL,
    title character varying(100),
    image_url character varying(255) NOT NULL,
    link_url character varying(255),
    is_active boolean DEFAULT true,
    display_order integer DEFAULT 0
);


ALTER TABLE public.carousel_banners OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 17619)
-- Name: carousel_banners_banner_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.carousel_banners_banner_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.carousel_banners_banner_id_seq OWNER TO postgres;

--
-- TOC entry 5047 (class 0 OID 0)
-- Dependencies: 229
-- Name: carousel_banners_banner_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.carousel_banners_banner_id_seq OWNED BY public.carousel_banners.banner_id;


--
-- TOC entry 238 (class 1259 OID 17670)
-- Name: gallery; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.gallery (
    gallery_id integer NOT NULL,
    title character varying(200) NOT NULL,
    description text,
    image_url character varying(255),
    category character varying(50),
    status character varying(20) DEFAULT 'active'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.gallery OWNER TO postgres;

--
-- TOC entry 237 (class 1259 OID 17669)
-- Name: gallery_gallery_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.gallery_gallery_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.gallery_gallery_id_seq OWNER TO postgres;

--
-- TOC entry 5048 (class 0 OID 0)
-- Dependencies: 237
-- Name: gallery_gallery_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.gallery_gallery_id_seq OWNED BY public.gallery.gallery_id;


--
-- TOC entry 226 (class 1259 OID 17592)
-- Name: guest_books; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.guest_books (
    guest_id integer NOT NULL,
    full_name character varying(100) NOT NULL,
    institution character varying(100) NOT NULL,
    email character varying(100),
    phone_number character varying(20) NOT NULL,
    message text NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.guest_books OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 17591)
-- Name: guest_books_guest_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.guest_books_guest_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.guest_books_guest_id_seq OWNER TO postgres;

--
-- TOC entry 5049 (class 0 OID 0)
-- Dependencies: 225
-- Name: guest_books_guest_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.guest_books_guest_id_seq OWNED BY public.guest_books.guest_id;


--
-- TOC entry 224 (class 1259 OID 17579)
-- Name: loans; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.loans (
    loan_id integer NOT NULL,
    borrower_name character varying(100) NOT NULL,
    borrower_contact character varying(50) NOT NULL,
    borrower_email character varying(100),
    institution character varying(100),
    asset_id integer NOT NULL,
    qty integer DEFAULT 1,
    start_time timestamp without time zone NOT NULL,
    end_time timestamp without time zone NOT NULL,
    status character varying(50) DEFAULT 'pending'::character varying,
    admin_note text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.loans OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 17578)
-- Name: loans_loan_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.loans_loan_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.loans_loan_id_seq OWNER TO postgres;

--
-- TOC entry 5050 (class 0 OID 0)
-- Dependencies: 223
-- Name: loans_loan_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.loans_loan_id_seq OWNED BY public.loans.loan_id;


--
-- TOC entry 232 (class 1259 OID 17631)
-- Name: partners; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.partners (
    partner_id integer NOT NULL,
    name character varying(100) NOT NULL,
    logo_url character varying(255),
    website_url character varying(255),
    description text,
    status character varying(20) DEFAULT 'active'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.partners OWNER TO postgres;

--
-- TOC entry 231 (class 1259 OID 17630)
-- Name: partners_partner_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.partners_partner_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.partners_partner_id_seq OWNER TO postgres;

--
-- TOC entry 5051 (class 0 OID 0)
-- Dependencies: 231
-- Name: partners_partner_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.partners_partner_id_seq OWNED BY public.partners.partner_id;


--
-- TOC entry 228 (class 1259 OID 17602)
-- Name: posts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.posts (
    post_id integer NOT NULL,
    title character varying(200) NOT NULL,
    slug character varying(200),
    content text,
    thumbnail_url character varying(255),
    status character varying(50) DEFAULT 'draft'::character varying,
    author_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    category character varying(50) DEFAULT 'General'::character varying,
    publish_date timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    activity_date date,
    location character varying(255),
    link character varying(255)
);


ALTER TABLE public.posts OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 17601)
-- Name: posts_post_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.posts_post_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.posts_post_id_seq OWNER TO postgres;

--
-- TOC entry 5052 (class 0 OID 0)
-- Dependencies: 227
-- Name: posts_post_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.posts_post_id_seq OWNED BY public.posts.post_id;


--
-- TOC entry 234 (class 1259 OID 17640)
-- Name: products; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.products (
    product_id integer NOT NULL,
    name character varying(100) NOT NULL,
    description text,
    image_url character varying(255),
    link_demo character varying(255),
    price numeric(12,2) DEFAULT 0,
    category character varying(50) DEFAULT 'Software'::character varying,
    status character varying(20) DEFAULT 'active'::character varying
);


ALTER TABLE public.products OWNER TO postgres;

--
-- TOC entry 233 (class 1259 OID 17639)
-- Name: products_product_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.products_product_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.products_product_id_seq OWNER TO postgres;

--
-- TOC entry 5053 (class 0 OID 0)
-- Dependencies: 233
-- Name: products_product_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.products_product_id_seq OWNED BY public.products.product_id;


--
-- TOC entry 236 (class 1259 OID 17649)
-- Name: team_members; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.team_members (
    member_id integer NOT NULL,
    user_id integer,
    name character varying(100) NOT NULL,
    "position" character varying(100),
    photo_url character varying(255),
    address text,
    phone_number character varying(50),
    public_email character varying(100),
    social_links jsonb,
    profile_details jsonb,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    bio text,
    status character varying(20) DEFAULT 'active'::character varying
);


ALTER TABLE public.team_members OWNER TO postgres;

--
-- TOC entry 235 (class 1259 OID 17648)
-- Name: team_members_member_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.team_members_member_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.team_members_member_id_seq OWNER TO postgres;

--
-- TOC entry 5054 (class 0 OID 0)
-- Dependencies: 235
-- Name: team_members_member_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.team_members_member_id_seq OWNED BY public.team_members.member_id;


--
-- TOC entry 218 (class 1259 OID 17510)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    user_id integer NOT NULL,
    username character varying(50),
    password_hash character varying(255) NOT NULL,
    full_name character varying(100) NOT NULL,
    identification_number character varying(50),
    institution character varying(100),
    email character varying(100),
    role public.user_role DEFAULT 'member'::public.user_role,
    student_type public.student_type DEFAULT 'regular'::public.student_type,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    nidn character varying(30)
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 217 (class 1259 OID 17509)
-- Name: users_user_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_user_id_seq OWNER TO postgres;

--
-- TOC entry 5055 (class 0 OID 0)
-- Dependencies: 217
-- Name: users_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_user_id_seq OWNED BY public.users.user_id;


--
-- TOC entry 220 (class 1259 OID 17530)
-- Name: visitor_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.visitor_logs (
    log_id integer NOT NULL,
    ip_address character varying(45) NOT NULL,
    user_agent text,
    page_url character varying(255),
    access_time timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.visitor_logs OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 17529)
-- Name: visitor_logs_log_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.visitor_logs_log_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.visitor_logs_log_id_seq OWNER TO postgres;

--
-- TOC entry 5056 (class 0 OID 0)
-- Dependencies: 219
-- Name: visitor_logs_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.visitor_logs_log_id_seq OWNED BY public.visitor_logs.log_id;


--
-- TOC entry 4808 (class 2604 OID 17693)
-- Name: activities activity_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activities ALTER COLUMN activity_id SET DEFAULT nextval('public.activities_activity_id_seq'::regclass);


--
-- TOC entry 4811 (class 2604 OID 17717)
-- Name: assets asset_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.assets ALTER COLUMN asset_id SET DEFAULT nextval('public.assets_asset_id_seq'::regclass);


--
-- TOC entry 4778 (class 2604 OID 17566)
-- Name: attendance_logs log_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attendance_logs ALTER COLUMN log_id SET DEFAULT nextval('public.attendance_logs_log_id_seq'::regclass);


--
-- TOC entry 4792 (class 2604 OID 17623)
-- Name: carousel_banners banner_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.carousel_banners ALTER COLUMN banner_id SET DEFAULT nextval('public.carousel_banners_banner_id_seq'::regclass);


--
-- TOC entry 4805 (class 2604 OID 17673)
-- Name: gallery gallery_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gallery ALTER COLUMN gallery_id SET DEFAULT nextval('public.gallery_gallery_id_seq'::regclass);


--
-- TOC entry 4785 (class 2604 OID 17595)
-- Name: guest_books guest_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.guest_books ALTER COLUMN guest_id SET DEFAULT nextval('public.guest_books_guest_id_seq'::regclass);


--
-- TOC entry 4781 (class 2604 OID 17582)
-- Name: loans loan_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.loans ALTER COLUMN loan_id SET DEFAULT nextval('public.loans_loan_id_seq'::regclass);


--
-- TOC entry 4795 (class 2604 OID 17634)
-- Name: partners partner_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.partners ALTER COLUMN partner_id SET DEFAULT nextval('public.partners_partner_id_seq'::regclass);


--
-- TOC entry 4787 (class 2604 OID 17605)
-- Name: posts post_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.posts ALTER COLUMN post_id SET DEFAULT nextval('public.posts_post_id_seq'::regclass);


--
-- TOC entry 4798 (class 2604 OID 17643)
-- Name: products product_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products ALTER COLUMN product_id SET DEFAULT nextval('public.products_product_id_seq'::regclass);


--
-- TOC entry 4802 (class 2604 OID 17652)
-- Name: team_members member_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.team_members ALTER COLUMN member_id SET DEFAULT nextval('public.team_members_member_id_seq'::regclass);


--
-- TOC entry 4770 (class 2604 OID 17513)
-- Name: users user_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN user_id SET DEFAULT nextval('public.users_user_id_seq'::regclass);


--
-- TOC entry 4776 (class 2604 OID 17533)
-- Name: visitor_logs log_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visitor_logs ALTER COLUMN log_id SET DEFAULT nextval('public.visitor_logs_log_id_seq'::regclass);


--
-- TOC entry 5036 (class 0 OID 17690)
-- Dependencies: 240
-- Data for Name: activities; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.activities (activity_id, activity_type, title, description, user_id, activity_date, location, status, created_at, updated_at, link) FROM stdin;
3	Other	try		1	2025-12-02	\N	completed	2025-12-02 13:12:23.892248	2025-12-02 17:29:29.30502	https://youtu.be/eis5aTweBHs
\.


--
-- TOC entry 5038 (class 0 OID 17714)
-- Dependencies: 242
-- Data for Name: assets; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.assets (asset_id, name, category, description, total_quantity, available_quantity, capacity, image_url, is_active, created_at) FROM stdin;
\.


--
-- TOC entry 5018 (class 0 OID 17563)
-- Dependencies: 222
-- Data for Name: attendance_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.attendance_logs (log_id, user_id, date, check_in_time, check_out_time, photo_proof, location_note, created_at) FROM stdin;
\.


--
-- TOC entry 5026 (class 0 OID 17620)
-- Dependencies: 230
-- Data for Name: carousel_banners; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.carousel_banners (banner_id, title, image_url, link_url, is_active, display_order) FROM stdin;
\.


--
-- TOC entry 5034 (class 0 OID 17670)
-- Dependencies: 238
-- Data for Name: gallery; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.gallery (gallery_id, title, description, image_url, category, status, created_at) FROM stdin;
1	-		https://let.polinema.ac.id/assets/images/whatsapp-image-2023-12-06-at-15.39.02-d074d025.jpeg	events	active	2025-12-02 09:42:29.412484
2	.		https://let.polinema.ac.id/assets/images/20240919-190114.jpg	documentation	active	2025-12-04 14:18:27.998887
3	research 		https://let.polinema.ac.id/assets/images/whatsapp-image-2023-11-28-at-13.53.53-ad815996.jpg	research	active	2025-12-04 14:19:02.31239
\.


--
-- TOC entry 5022 (class 0 OID 17592)
-- Dependencies: 226
-- Data for Name: guest_books; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.guest_books (guest_id, full_name, institution, email, phone_number, message, created_at) FROM stdin;
1	Rafi Zeta F	Politeknik Negeri Malang	rafizf.fauzan25@gmail.com	081217035714	Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.	2025-12-04 14:11:24.354124
\.


--
-- TOC entry 5020 (class 0 OID 17579)
-- Dependencies: 224
-- Data for Name: loans; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.loans (loan_id, borrower_name, borrower_contact, borrower_email, institution, asset_id, qty, start_time, end_time, status, admin_note, created_at) FROM stdin;
\.


--
-- TOC entry 5028 (class 0 OID 17631)
-- Dependencies: 232
-- Data for Name: partners; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.partners (partner_id, name, logo_url, website_url, description, status, created_at) FROM stdin;
\.


--
-- TOC entry 5024 (class 0 OID 17602)
-- Dependencies: 228
-- Data for Name: posts; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.posts (post_id, title, slug, content, thumbnail_url, status, author_id, created_at, category, publish_date, activity_date, location, link) FROM stdin;
2	Test article 2025	test-article-2025	Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.		published	\N	2025-11-29 19:06:13.259055	Research	2025-11-29 12:05:00	\N	\N	\N
3	Lorem Ipsum	lorem-ipsum	Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.\r\n\r\nLorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-01-08-at-10.49.24-71aa8530.jpg	published	\N	2025-12-04 14:19:48.947833	Research	2025-12-04 07:19:00	\N	\N	\N
\.


--
-- TOC entry 5030 (class 0 OID 17640)
-- Dependencies: 234
-- Data for Name: products; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.products (product_id, name, description, image_url, link_demo, price, category, status) FROM stdin;
2	viat map application	application for viat map	https://let.polinema.ac.id/assets/images/viat-map.png	https://apkpure.net/hatsune-miku-colorful-stage-2024-japan/com.sega.pjsekai	0.00		
\.


--
-- TOC entry 5032 (class 0 OID 17649)
-- Dependencies: 236
-- Data for Name: team_members; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.team_members (member_id, user_id, name, "position", photo_url, address, phone_number, public_email, social_links, profile_details, created_at, bio, status) FROM stdin;
3	\N	Usman Nurhasan, S.Kom., MT.	peneliti	uploads/team/1764837092_548.jpg	\N		usmannurhasan@polinema.ac.id	{"sinta": "https://sinta.kemdiktisaintek.go.id/authors/profile/6173177/", "linkedin": "https://www.linkedin.com/in/usman-nurhasan-b8677a16b", "google_scholar": "https://scholar.google.com/citations?user=PEaROTMAAAAJ&hl=id"}	{"nip": "198609232015041001", "nidn": "0023098604", "prodi": "Sistem Informasi Bisnis", "education": ["S1 — Sarjana Komputer Universitas Islam Negeri Maulana Malik Ibrahim (2010)", "S2 — Magister Teknik Universitas Brawijaya (2014)"], "certifications": []}	2025-12-04 13:43:36.490404		active
2	\N	Dr. Eng. Banni Satria Andoko	Ketua Laboratorium	uploads/team/1764837126_311.jpg	\N	(62) 813-5988-9181	ando@polinema.ac.id	{"orcid": "https://orcid.org/0000-0001-8174-6960", "scopus": "https://www.scopus.com/authid/detail.uri?authorId=57201994154", "linkedin": "https://www.linkedin.com/in/banniandoko/", "researchgate": "https://www.researchgate.net/profile/Banni-Andoko", "google_scholar": "https://scholar.google.com/citations?user=jetyPtUAAAAJ&hl=en"}	{"nip": "198108092010121002", "nidn": "0009088107", "prodi": "Rekayasa Teknologi Informasi", "education": ["S1 Teknik Informatika = STMIK PPKIA Pradnya paramita", "S2 Manajemen Sistem Informasi = Universitas Gunadarma", "S3 Information Engineering = Hiroshima university"], "certifications": []}	2025-12-02 08:27:34.539783		active
\.


--
-- TOC entry 5014 (class 0 OID 17510)
-- Dependencies: 218
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (user_id, username, password_hash, full_name, identification_number, institution, email, role, student_type, is_active, created_at, updated_at, nidn) FROM stdin;
1	admin	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	Super Administrator	213131	Politeknik Negeri Malang	admin@letlab.id	admin	\N	t	2025-11-21 19:20:02.164625	2025-12-02 09:37:42.101254	\N
2	dosen	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	Banni Satria Andoko	123131	Politeknik Negeri Malang	ando@polinema.ac.id	dosen	\N	t	2025-11-21 19:20:02.164625	2025-12-04 19:11:24.125335	199001012019031001
3	mahasiswa	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	Budi Santoso	2241720001	Politeknik Negeri Malang	budi@polinema.ac.id	member	regular	t	2025-11-21 19:20:02.164625	2025-11-21 19:20:02.164625	\N
\.


--
-- TOC entry 5016 (class 0 OID 17530)
-- Dependencies: 220
-- Data for Name: visitor_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.visitor_logs (log_id, ip_address, user_agent, page_url, access_time) FROM stdin;
\.


--
-- TOC entry 5057 (class 0 OID 0)
-- Dependencies: 239
-- Name: activities_activity_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.activities_activity_id_seq', 3, true);


--
-- TOC entry 5058 (class 0 OID 0)
-- Dependencies: 241
-- Name: assets_asset_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.assets_asset_id_seq', 1, false);


--
-- TOC entry 5059 (class 0 OID 0)
-- Dependencies: 221
-- Name: attendance_logs_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.attendance_logs_log_id_seq', 1, true);


--
-- TOC entry 5060 (class 0 OID 0)
-- Dependencies: 229
-- Name: carousel_banners_banner_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.carousel_banners_banner_id_seq', 1, false);


--
-- TOC entry 5061 (class 0 OID 0)
-- Dependencies: 237
-- Name: gallery_gallery_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.gallery_gallery_id_seq', 3, true);


--
-- TOC entry 5062 (class 0 OID 0)
-- Dependencies: 225
-- Name: guest_books_guest_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.guest_books_guest_id_seq', 1, true);


--
-- TOC entry 5063 (class 0 OID 0)
-- Dependencies: 223
-- Name: loans_loan_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.loans_loan_id_seq', 1, false);


--
-- TOC entry 5064 (class 0 OID 0)
-- Dependencies: 231
-- Name: partners_partner_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.partners_partner_id_seq', 4, true);


--
-- TOC entry 5065 (class 0 OID 0)
-- Dependencies: 227
-- Name: posts_post_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.posts_post_id_seq', 3, true);


--
-- TOC entry 5066 (class 0 OID 0)
-- Dependencies: 233
-- Name: products_product_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.products_product_id_seq', 2, true);


--
-- TOC entry 5067 (class 0 OID 0)
-- Dependencies: 235
-- Name: team_members_member_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.team_members_member_id_seq', 3, true);


--
-- TOC entry 5068 (class 0 OID 0)
-- Dependencies: 217
-- Name: users_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_user_id_seq', 9, true);


--
-- TOC entry 5069 (class 0 OID 0)
-- Dependencies: 219
-- Name: visitor_logs_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.visitor_logs_log_id_seq', 1, false);


--
-- TOC entry 4860 (class 2606 OID 17701)
-- Name: activities activities_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT activities_pkey PRIMARY KEY (activity_id);


--
-- TOC entry 4862 (class 2606 OID 17726)
-- Name: assets assets_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.assets
    ADD CONSTRAINT assets_pkey PRIMARY KEY (asset_id);


--
-- TOC entry 4840 (class 2606 OID 17572)
-- Name: attendance_logs attendance_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attendance_logs
    ADD CONSTRAINT attendance_logs_pkey PRIMARY KEY (log_id);


--
-- TOC entry 4850 (class 2606 OID 17629)
-- Name: carousel_banners carousel_banners_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.carousel_banners
    ADD CONSTRAINT carousel_banners_pkey PRIMARY KEY (banner_id);


--
-- TOC entry 4858 (class 2606 OID 17679)
-- Name: gallery gallery_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gallery
    ADD CONSTRAINT gallery_pkey PRIMARY KEY (gallery_id);


--
-- TOC entry 4844 (class 2606 OID 17600)
-- Name: guest_books guest_books_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.guest_books
    ADD CONSTRAINT guest_books_pkey PRIMARY KEY (guest_id);


--
-- TOC entry 4842 (class 2606 OID 17590)
-- Name: loans loans_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.loans
    ADD CONSTRAINT loans_pkey PRIMARY KEY (loan_id);


--
-- TOC entry 4852 (class 2606 OID 17638)
-- Name: partners partners_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.partners
    ADD CONSTRAINT partners_pkey PRIMARY KEY (partner_id);


--
-- TOC entry 4846 (class 2606 OID 17611)
-- Name: posts posts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.posts
    ADD CONSTRAINT posts_pkey PRIMARY KEY (post_id);


--
-- TOC entry 4848 (class 2606 OID 17613)
-- Name: posts posts_slug_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.posts
    ADD CONSTRAINT posts_slug_key UNIQUE (slug);


--
-- TOC entry 4854 (class 2606 OID 17647)
-- Name: products products_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_pkey PRIMARY KEY (product_id);


--
-- TOC entry 4856 (class 2606 OID 17657)
-- Name: team_members team_members_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.team_members
    ADD CONSTRAINT team_members_pkey PRIMARY KEY (member_id);


--
-- TOC entry 4820 (class 2606 OID 17871)
-- Name: users unique_fullname; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT unique_fullname UNIQUE (full_name);


--
-- TOC entry 4822 (class 2606 OID 17867)
-- Name: users unique_id_number; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT unique_id_number UNIQUE (identification_number);


--
-- TOC entry 4824 (class 2606 OID 17873)
-- Name: users unique_identification_number; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT unique_identification_number UNIQUE (identification_number);


--
-- TOC entry 4826 (class 2606 OID 17869)
-- Name: users unique_username; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT unique_username UNIQUE (username);


--
-- TOC entry 4828 (class 2606 OID 17528)
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- TOC entry 4830 (class 2606 OID 17742)
-- Name: users users_nidn_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_nidn_key UNIQUE (nidn);


--
-- TOC entry 4832 (class 2606 OID 17865)
-- Name: users users_nim_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_nim_key UNIQUE (identification_number);


--
-- TOC entry 4834 (class 2606 OID 17522)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (user_id);


--
-- TOC entry 4836 (class 2606 OID 17524)
-- Name: users users_username_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- TOC entry 4838 (class 2606 OID 17538)
-- Name: visitor_logs visitor_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visitor_logs
    ADD CONSTRAINT visitor_logs_pkey PRIMARY KEY (log_id);


--
-- TOC entry 4863 (class 2606 OID 17573)
-- Name: attendance_logs attendance_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attendance_logs
    ADD CONSTRAINT attendance_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE CASCADE;


--
-- TOC entry 4864 (class 2606 OID 17727)
-- Name: loans fk_loans_asset; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.loans
    ADD CONSTRAINT fk_loans_asset FOREIGN KEY (asset_id) REFERENCES public.assets(asset_id) ON DELETE CASCADE;


--
-- TOC entry 4867 (class 2606 OID 17702)
-- Name: activities fk_user; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 4865 (class 2606 OID 17614)
-- Name: posts posts_author_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.posts
    ADD CONSTRAINT posts_author_id_fkey FOREIGN KEY (author_id) REFERENCES public.users(user_id);


--
-- TOC entry 4866 (class 2606 OID 17658)
-- Name: team_members team_members_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.team_members
    ADD CONSTRAINT team_members_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(user_id);


-- Completed on 2025-12-04 19:50:31

--
-- PostgreSQL database dump complete
--

\unrestrict jRgTLxwDs7h4hNUtvbCUtBLZ7zBKasulLAyCDcMYzqu7ho3lwoa8xRRh9i7Idoe

