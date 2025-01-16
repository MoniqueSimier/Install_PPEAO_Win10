--
-- PostgreSQL database dump
--

SET client_encoding = 'LATIN9';
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

--
-- Data for Name: meta_systemes; Type: TABLE DATA; Schema: public; Owner: devppeao
--

COPY meta_systemes (meta_id, ref_systeme_id, doc_type, file_path, doc_titre, doc_description) FROM stdin;
64	10	document	files/Descriptif_Gambie_2001_2002_peches_artisanales.pdf	Pechart_Gambie	Descriptif suivi peches artisanales Gambie en 2001 2002
42	3	document	files/Systeme_Ebrie_couleur_codestat.pdf	EbrieStationsMap	Carte des stations d echantillonnage a la senne tournante en lagune Ebrie
59	3	document	files/Paper_Albaret_1999.PDF	Paper_Albaret_1999_Ebrie	Les peuplements des estuaires et des lagunes
50	8	document	files/Systeme_Saloum_NetB_codes_alpha_zones.pdf	SaloumStationAlphaMap	Carte des stations d echantillonnage a la senne tournante dans le Sine Saloum avec leur code alphanumerique
65	3	document	files/Descriptif_Ebrie_1978_1986_peches_artisanales.pdf	Pechart_Ebrie	Descriptif suivi peches artisanales Lagune Ebrie 1978 1986
48	8	document	files/Systeme_Saloum_N&B_codes_chiffres_zones.pdf	SaloumStationsNumMap	Carte des stations d echantillonnage a la senne tournante dans le Sine Saloum avec leur code numerique
51	8	carte	images/Systeme_Saloum_carte_bioindics.png	SaloumStationsBioindic	Saloum Localisation des stations d echantillonnage du programme Bioindicateurs en 2005-2006
52	8	carte	images/Systeme_Saloum_Juveniles_couleur.jpg	SaloumStationsJuveniles	Saloum Localisation des stations d echantillonnage des peuplement juveniles
53	10	document	files/Paper_Albaret_et_al_ALR_2004.pdf	Paper_Albaret_Gambia_ALR	Fish diversity and distribution in the Gambia Estuary, West Africa, in relation to environmental variables
67	12	document	files/Descriptif_Casamance_2005_peches_artisanales.pdf	Pechart_Casamance	Descriptif Suivi Peches artisanales Casamance 2005
54	3	document	files/Paper_Ecoutin_et_al_ECSS_2005.pdf	Paper_Ecoutin_Ebrie_ECSS	Spatial vs temporal patterns in fish assemblages of a tropical estuarine coastal lake: the Ebrie Lagoon (Ivory Coast)
55	8	document	files/Paper_Ecoutin_etal_ECSS_2010.pdf	Paper_Ecoutin_Saloum_ECSS	Changes over a decade in fish assemblages exposed to both environmental and fishing constraints in the Sine Saloum estuary (Senegal)
56	8	document	files/Paper_Simier_et_al_ECSS_2004.pdf	Paper_Simier_Saloum_ECSS	Spatial and temporal structure of fish assemblages in an inverse estuary, the Sine Saloum system (Senegal)
57	10	document	files/Paper_Simier_et_al_ECSS_2006.pdf	Paper_Simier_Gambia_ECSS	The Gambia River estuary: a reference point for estuarine fish assemblages studies in West Africa
31	18	document	files/Descriptif_Arguin_2008_2010_peches_experimentales.pdf	PechexpArguin	Descriptif de l echantillonnage realise par peches scientifiques dans le banc d Arguin (Mauritanie) en 2008 et 2010
58	10	document	files/Paper_Vidy_et_al_ALR_2004.pdf	Paper_Vidy_Gambia_ALR	Juvenile fish assemblages in the creeks of the Gambia Estuary
32	17	document	files/Descriptif_Bamboung_2003_2012_peches_experimentales.pdf	PechexpBamboung	Descriptif de l echantillonnage realise par peches scientifiques dans l AMP du bolon de Bamboung entre 2003 et 2012
62	17	document	files/Paper_Ecoutin_et_al_2014_OCM.pdf	Paper_Ecoutin_Bamboung_2014	Ecological fields experiment of short-term effects of fishing ban on fish assemblages in a tropical estuary
25	3	document	files/Descriptif_Ebrie_1979_1982_peches_experimentales.pdf	PechexpEbrie	Descriptif de l echantillonnage realise par peches scientifiques dans la lagune Ebrie entre 1979 et 1982
35	18	figure	images/Systeme_Arguin_map.jpg	ArguinMap	Carte du Banc d Arguin en Mauritanie
36	17	figure	images/Systeme_Bamboung_carte2003-2007.jpg	BamboungMap	Carte de l AMP du Bolon Bamboung Sine Saloum Senegal
26	10	document	files/Descriptif_Gambie_2000_2003_peches_experimentales.pdf	PechexpGambie	Descriptif de l echantillonnage realise par peches scientifiques dans l estuaire du fleuve Gambie entre 2000 et 2003
24	19	document	files/Descriptif_Urok_2011_2013_peches_experimentales.pdf	PechexpIlesUrok	Descriptif de l echantillonnage realise par peches scientifiques dans l AMP des Iles Urok en 2011 2013
39	10	figure	images/Systeme_Gambie_Juveniles_couleur.jpg	GambieJuvenileMap	Carte des stations d echantillonnage des juveniles en Gambie
34	8	document	files/Descriptif_Saloum_2001_2006_peches_experimentales.pdf	PechexpSaloum0106	Descriptif de l echantillonnage realise par peches scientifiques dans le delta du Sine Saloum entre 2001 et 2006
33	8	document	files/Descriptif_Saloum_1990_1997_peches_experimentales.pdf	PechexpSaloum9097	Descriptif de l echantillonnage realise par peches scientifiques dans le delta du Sine Saloum entre 1990 et 1997
43	3	document	files/Systeme_Ebrie_NetB_secteurs.pdf	EbrieSecteursMap	Carte des secteurs en lagune Ebrie
44	10	document	files/Systeme_GambiePechexp_Stations.pdf	GambieStationsMap	Carte des stations d echantillonnage en Gambie
45	15	document	files/Systeme_GuineeBissao_Bijagos.PDF	BijagosMap	Carte des stations d echantillonnage dans l archipel des Bijagos en 1993
63	17	document	files/Paper_Sadio_etal_2015_OCMA.pdf	Paper_Sadio_Bamboung_2015	Effects of a marine protected area on tropical fish assemblages: comparison between protected and unprotected sites in Senegal
46	16	document	files/Descriptif_Guinee_Bissau_1993_peches_experimentales.pdf	RioBubaMap	Carte des stations d echantillonnage dans le Rio Grande de Buba en 1993
\.


--
-- PostgreSQL database dump complete
--

