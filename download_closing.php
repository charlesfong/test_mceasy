DECLARE
     last_year_month varchar;
     bulan varchar;
     tahun varchar;
     tanggal varchar;
BEGIN
     if exists(select pid from pg_stat_activity where pid <> pg_backend_pid() and query ilike '%f_close_sc%') then
        --perform pg_sleep(10);
        RAISE EXCEPTION 'procedure f_close_sc % is busy', $1;
     else
     last_year_month:= param_year_month::INTEGER-1;
     bulan := substr(param_year_month,5);
     tahun := substr(param_year_month,0,5);
     tanggal :=(CONCAT (tahun,'-',bulan,'-','01'));

     insert into inventory.sc_monthly_closing (loccd,year_month,prdcd,cn_id,opening,receiving_trx,
		inv_trx,cb_trx,cr_trx,adj_trx,ioc_trx,
		dsp_trx,foc_trx,return_trx,closing,sp_trx)
		select z.code,param_year_month,y.prdcd,'MY' as cn_id,0,0,0,0,0,0,0,0,0,0,0,0 from
		(SELECT distinct code FROM a.trdt<'$tgl1'sub_mssc order by code asc) z,
		(select trim(a.prdcd)as prdcd,a.dp as price from a.trdt<'$tgl1'msprd a left join a.trdt<'$tgl1'msprd_extra b on trim(b.prdcd)=trim(a.prdcd) ) y;


--UPDATE OPENING
            update inventory.sc_monthly_closing set opening=z.qty,closing=closing+z.qty
	    from (
		     select zz.loccd,zz.prdcd,sum(zz.qty)as qty
            from(
            select a.note3 as loccd, c.inv_prdcd as prdcd, coalesce(sum(b.delivered_qty*c.inv_qty),0)as qty
            from a.trdt<'$tgl1'newmsivtrh a inner join a.trdt<'$tgl1'newmsivtrd b using(trivcd)
            inner join a.trdt<'$tgl1'msprd_items c on c.prdcd=b.prdcd
            where (a.trdt<tanggal::DATE)
            group by 1,2
            UNION ALL
            select a.note3 as loccd, c.inv_prdcd as prdcd, - coalesce(sum(b.qty*c.inv_qty),0)as qty
            from a.trdt<'$tgl1'newsctrh a inner join a.trdt<'$tgl1'newsctrd b using(trcd)
            inner join a.trdt<'$tgl1'msprd_items c on c.prdcd=b.prdcd
            where (a.trdt<tanggal::DATE)
            group by 1,2
            UNION ALL
            select a.sccode as loccd, c.inv_prdcd as prdcd, - coalesce(sum(b.qty*c.inv_qty),0)as qty
            from a.trdt<'$tgl1'ioc_newsctrh a inner join a.trdt<'$tgl1'ioc_newsctrd b using(trcd)
            inner join a.trdt<'$tgl1'msprd_items c on c.prdcd=b.prdcd
            where (a.trdt<tanggal::DATE)
            group by 1,2
            UNION ALL
            select a.code as loccd, c.inv_prdcd as prdcd, coalesce(sum(b.qty*c.inv_qty),0)as qty
            from a.trdt<'$tgl1'adsctrh a inner join a.trdt<'$tgl1'adsctrd b using(trcd)
            inner join a.trdt<'$tgl1'msprd_items c on c.prdcd=b.prdcd
            where (a.trdt<tanggal::DATE)
            group by 1,2) zz            
            group by 1,2            
            order by 1,2
	    ) z
	    where z.loccd=inventory.sc_monthly_closing.loccd and z.prdcd=inventory.sc_monthly_closing.prdcd 
	    and inventory.sc_monthly_closing.year_month=param_year_month;

--SC RECEIVING TRX
           update inventory.sc_monthly_closing set receiving_trx=z.qty,closing=closing+z.qty 
           from (
	   select a.note3 as loccd, c.inv_prdcd as prdcd, coalesce(sum(b.delivered_qty*c.inv_qty),0)as qty
           from a.trdt<'$tgl1'newmsivtrh a inner join a.trdt<'$tgl1'newmsivtrd b using(trivcd)
           inner join a.trdt<'$tgl1'msprd_items c on c.prdcd=b.prdcd
           where (a.trdt between tanggal::DATE and (date_trunc('MONTH', (param_year_month||'01')::date) + INTERVAL '1 MONTH - 1 day')::DATE)
           group by 1,2) z
	   where z.loccd=inventory.sc_monthly_closing.loccd and 
	   z.prdcd=inventory.sc_monthly_closing.prdcd and inventory.sc_monthly_closing.year_month= param_year_month;


--SC CASH BILL TRX
           update inventory.sc_monthly_closing set cb_trx=z.qty,closing=closing-z.qty 
	   from (
	   select a.note3 as loccd, c.inv_prdcd as prdcd, coalesce(sum(b.qty*c.inv_qty),0)as qty
           from a.trdt<'$tgl1'newsctrh a inner join a.trdt<'$tgl1'newsctrd b using(trcd)
           inner join a.trdt<'$tgl1'msprd_items c on c.prdcd=b.prdcd
           where (a.trdt between tanggal::DATE and (date_trunc('MONTH', (param_year_month||'01')::date) + INTERVAL '1 MONTH - 1 day')::DATE)
           group by 1,2) z
	   where z.loccd=inventory.sc_monthly_closing.loccd and 
	   z.prdcd=inventory.sc_monthly_closing.prdcd and inventory.sc_monthly_closing.year_month= param_year_month;
        

--SC IOC TRX
	   update inventory.sc_monthly_closing set ioc_trx=z.qty,closing=closing-z.qty 
	   from (
	   select a.sccode as loccd, c.inv_prdcd as prdcd, coalesce(sum(b.qty*c.inv_qty),0)as qty
           from a.trdt<'$tgl1'ioc_newsctrh a inner join a.trdt<'$tgl1'ioc_newsctrd b using(trcd)
           inner join a.trdt<'$tgl1'msprd_items c on c.prdcd=b.prdcd
           where (a.trdt between tanggal::DATE and (date_trunc('MONTH', (param_year_month||'01')::date) + INTERVAL '1 MONTH - 1 day')::DATE)
           group by 1,2) z
	   where z.loccd=inventory.sc_monthly_closing.loccd and 
	   z.prdcd=inventory.sc_monthly_closing.prdcd and inventory.sc_monthly_closing.year_month= param_year_month;

--SC ADJ TRX
	   update inventory.sc_monthly_closing set adj_trx=z.qty,closing=closing-z.qty 
	   from (
	   select a.code as loccd, c.inv_prdcd as prdcd, coalesce(sum(b.qty*c.inv_qty),0)as qty
           from a.trdt<'$tgl1'adsctrh a inner join a.trdt<'$tgl1'adsctrd b using(trcd)
           inner join a.trdt<'$tgl1'msprd_items c on c.prdcd=b.prdcd
           where (a.trdt between tanggal::DATE and (date_trunc('MONTH', (param_year_month||'01')::date) + INTERVAL '1 MONTH - 1 day')::DATE)
           group by 1,2) z
	   where z.loccd=inventory.sc_monthly_closing.loccd and 
	   z.prdcd=inventory.sc_monthly_closing.prdcd and inventory.sc_monthly_closing.year_month= param_year_month;

--SC CLOSING VALUE
	   update inventory.sc_monthly_closing set closing=opening+receiving_trx-cb_trx-ioc_trx-adj_trx
	   where year_month= param_year_month;

           update a.trdt<'$tgl1'closing_daysetup set updatenm='cron',updatedt='now()' where cn_id='MY';

           return tanggal;

           end if;
END