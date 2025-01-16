UPDATE systeme, Systeme_corresp SET systeme.SYS_NOM = Systeme_corresp!PechexpSYS_NOM WHERE (((systeme.SYS_NUM)=Systeme_corresp!IdSysteme) AND ((Systeme_corresp.PechexpSYS_NUM) Is Not Null));
UPDATE pays SET pays.PAYS_NOM = 'Cote d Ivoire' WHERE (((pays.PAYS_NOM)='Cote d''Ivoire'));
